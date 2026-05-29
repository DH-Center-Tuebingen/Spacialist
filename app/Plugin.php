<?php

namespace App;

use App\Plugin\PluginManifest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plugin extends Model {
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'version',
        'uuid',
        'installed_at',
        'update_available',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'installed_at' => 'datetime',
    ];

    public static function getInstalledPlugins(): Collection {
        return self::whereNotNull('installed_at')->get();
    }

    public static function isInstalled($name): bool {
        return self::whereNotNull('installed_at')->where('name', $name)->exists();
    }

    public function slugName(): string {
        return strtolower(str_replace(' ', '', $this->name));
    }

    public static function updateOrCreateFromManifest(PluginManifest $manifest): Plugin {
        $name = $manifest->getName();
        $plugin = self::where('name', $name)->first();

        $isCreation = false;
        if(!isset($plugin)) {
            $plugin = new self();
            $plugin->uuid = Str::uuid();
            $plugin->name = $name;
            $isCreation = true;
        }

        $plugin->version = $manifest->getVersion();
        $plugin->metadata = [
            'authors' => $manifest->getAuthors(),
            'description' => $manifest->getDescription(),
            'licence' => $manifest->getLicence(),
        ];

        if(!$isCreation) {
            $plugin->updateUpdateAvailable($manifest->getVersion());
        }
        $plugin->save();

        return $plugin;
    }

    public function updateUpdateAvailable($fromInfoVersion): void {
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
                $this->update_available = NULL;
            }
        }
    }
}
