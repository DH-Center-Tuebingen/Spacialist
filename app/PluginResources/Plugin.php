<?php

namespace App\PluginResources;

use App\Permission; 
use App\PluginResources\Presets\RolePresetPlugin;
use App\Preference;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Plugin extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    protected $metadataFields = [
        'authors',
        'description',
        'licence',
        'title',
    ];
    
    private $presets = [];
    
    public function __construct($attributes = []) {
        parent::__construct($attributes);
        
        $this->presets = [
            RolePresetPlugin::class,
        ];
    }

    public static function isInstalled($name) {
        return self::whereNotNull('installed_at')->where('name', $name)->exists();
    }

    public static function getInstalled() {
        return self::whereNotNull('installed_at')->get();
    }

    public function slugName() {
        return Str::slug($this->name);
    }

    public function publicName($withPath = true) {
        $slug = $this->slugName();
        $uuid = $this->uuid;
        $name = "${slug}-${uuid}.js";
        if($withPath) {
            $name = "plugins/$name";
        }
        return $name;
    }

    public static function getInfo($path, $isString = false) {
        $xmlString = '';
        if(!$isString) {
            
            $manifestLocations = [
                'App/info.xml', // Legacy location of the 'info.xml' file
                'manifest.xml', // This is the potential new location of the 'info.xml' file
            ];
            
            while(count($manifestLocations) > 0){
                $location = array_shift($manifestLocations);
                $infoPath = Str::finish($path, '/') . $location;
                if(File::isFile($infoPath)){
                    $xmlString = file_get_contents($infoPath);
                    break;
                }
            }
            
            if($xmlString == ''){
                return false;
            }
        } else {
            $xmlString = $path;
        }

        $xmlObject = simplexml_load_string($xmlString);
        
        return json_decode(json_encode($xmlObject), true);
    }

    public function getMetadata() {
        $info = self::getInfo(base_path("app/Plugins/$this->name"));
        if($info !== false) {
            $metadata = [];
            foreach($this->metadataFields as $field) {
                if($field == 'authors') {
                    $authors = $info[$field]['author'];
                    $metadata[$field] = is_array($authors) ? $authors : [$authors];
                } else {
                    $metadata[$field] = $info[$field];
                }
            }
            return $metadata;
        } else {
            return [];
        }
    }
    
    public function getPath(array $parts = []){
        $path = base_path("app/Plugins/$this->name");
        if(count($parts) > 0){
            $path = Str::finish($path, '/');
            $path .= implode('/', $parts);
        }
        return $path;
    }

    public function getChangelog($since = null) {
        $changelog = $this->getPath(['CHANGELOG.md']);
        if(!File::isFile($changelog)) return '';
        $changes = file_get_contents($changelog);
        if(isset($since) && preg_match("/\\n#+\s(v\s?)?$since(\s-\s.+)?\\n/i", $changes, $matches, PREG_OFFSET_CAPTURE) !== false) {
            if(count($matches) > 0) {
                $changes = substr($changes, 0, $matches[0][1]);
            }
        }
        return $changes;
    }

    public static function updateOrCreateFromInfo(array $info) : Plugin {
        $id = $info['name'];
        $plugin = self::where('name', $id)->first();
    
        // discovered new Plugin, add it to DB
        if(!isset($plugin)) {
            $plugin = new self();
            $plugin->name = $id;
            $plugin->version = $info['version'];
            $plugin->uuid = Str::uuid();
            $plugin->save();
        } else {
            $plugin->updateUpdateState($info['version']);
        }

        return $plugin;
    }

    public static function updateState() : void {
        $pluginPath = base_path('app/Plugins');
        $availablePlugins = File::directories($pluginPath);

        self::discoverPlugins($availablePlugins);
        self::cleanupPlugins($availablePlugins);
    }

    public static function cleanupPlugins(array $list) : void {
        $pluginNames = [];

        foreach($list as $p) {
            $pluginNames[] = File::basename($p);
        }

        $nonExistingPlugins = self::whereNotIn('name', $pluginNames)->get();

        foreach($nonExistingPlugins as $removedPlugin) {
            $removedPlugin->handleRemove();
        }
    }

    public static function discoverPlugins(array $list) : void {
        foreach($list as $ap) {
            $info = self::getInfo($ap);
            if($info !== false) {
                self::updateOrCreateFromInfo($info);
            }
        }
    }

    public static function discoverPluginByName($name) : Plugin|null {
        $pluginPath = base_path("app/Plugins/$name");
        $info = self::getInfo($pluginPath);
        if($info === false) {
            return null;
        }

        $plugin = self::updateOrCreateFromInfo($info);

        return $plugin;
    }

    public function updateUpdateState($fromInfoVersion) {
        if($this->version != $fromInfoVersion) {
            // installed version splitted
            preg_match('/(\d+)\.(\d+).(\d+)(-.+)?/', $this->version, $iv);
            // available/latest version splitted
            preg_match('/(\d+)\.(\d+).(\d+)(-.+)?/', $fromInfoVersion, $lv);
    
            if(
                ($lv[1] > $iv[1] || $lv[2] > $iv[2] || $lv[3] > $iv[3]) ||
                (!isset($lv[4]) && isset($iv[4])) ||
                (isset($lv[4]) && isset($iv[4]) && $lv[4] > $iv[4])
            ) {
                $this->update_available = $fromInfoVersion;
            } else {
                $this->update_available = null;
            }
            $this->save();
        }
    }
    
    public function handleAdd(){
        Migration::use($this)->migrate();
        $this->addPermissions();
    }

    public function handleInstallation() {
        $this->publishScript();
        $this->installPresets();
        $this->installed_at = Carbon::now();
        $this->save();
    }

    //TODO:: Rework
    public function handleUpdate() {
        $oldVersion = $this->version;
        // TODO is it really the same as install?
        $this->handleInstallation();


        $info = self::getInfo(base_path("app/Plugins/$this->name"));
        $this->update_available = null;
        $this->version = $info['version'];
        $this->save();
        return $oldVersion;
    }

    public function handleUninstall() {
        $this->removeScript();
        $this->installed_at = null;
        $this->save();
    }

    public function handleRemove() {
        // if installed, first rollback migrations and delete all files and presets
        if(isset($this->installed_at)) {
            $this->handleUninstall();
            Migration::use($this)->rollback();
            $this->removePermissions();
            $this->uninstallPresets();
        }

        $this->removePreferences();
        sp_remove_dir($this->getPath());

        $this->delete();
    }

    public function getPermissions() {
        $pluginPermissionPath = base_path("app/Plugins/$this->name/App/permissions.json");
        if(!File::isFile($pluginPermissionPath)) {
            return [];
        }

        return json_decode(file_get_contents($pluginPermissionPath), true);
    }

    public function getPermissionGroups() {
        return array_keys($this->getPermissions());
    }
    
    public function getClassPath(array $parts){
        return "App\\Plugins\\$this->name\\" . implode('\\', $parts);
    }
    
    public function getProblemsAttribute(){
        $problems = [];
        
        if(!$this->hasScript()){
            $problems[] = "script_missing";
        }
        
        return $problems;
    }
    

    private function hasScript(){
        return Storage::exists($this->publicName());
    }
    
    private function publishScript() {
        $name = $this->name;
        $scriptPath = base_path("app/Plugins/$name/js/script.js");
        if(file_exists($scriptPath)) {
            $filepath = Storage::path($this->publicName());
            File::link($scriptPath, $filepath);
        } else {
            throw new \Exception("Script file not found at '$scriptPath'");
        }
    }

    private function removeScript() {
        $path = $this->publicName();
        if(Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    private function addPermissions() {
        $permGroups = $this->getPermissions();
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                $permission = new Permission();
                $permission->name = $group . "_" . $perm['name'];
                $permission->display_name = $perm['display_name'];
                $permission->description = $perm['description'];
                $permission->guard_name = 'web';
                $permission->save();
            }
        }
    }

    private function removePermissions() {
        $permGroups = $this->getPermissions();
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                Permission::where('name', $group . "_" . $perm['name'])->delete();
            }
        }
    }

    private function installPresets() {
        foreach($this->presets as $preset) {
            call_user_func([$preset, 'install'], $this);
        }
    }

    private function uninstallPresets() {
        foreach($this->presets as $preset) {
            call_user_func([$preset, 'uninstall'], $this);
        }
    }

    private function removePreferences() {
        $id = Str::kebab($this->name);
        Preference::where('label', 'ilike', "plugin.$id.%")->delete();
    }
    
    public function getErrors(){
        
        $requiredFiles = [
            'manifest.xml',
            'README.md',
            'CHANGELOG.md',
        ];
        
        $errors = [];
        foreach($requiredFiles as $file){
            if(!File::isFile($this->getPath([$file]))){
                $errors[] = "Missing required file: $file";
            }
        }
        
        return $errors;
        
    }
}
