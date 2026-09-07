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
        $existingPlugin = self::where('name', $name)->first();
        $newPlugin = null;

        $isCreation = false;
        if(!isset($existingPlugin)) {
            $newPlugin = new self();
            $newPlugin->uuid = Str::uuid();
            $newPlugin->name = $name;
            $isCreation = true;
        } else {
            $newPlugin = clone($existingPlugin);
        }

        if(version_compare($newPlugin->version, $manifest->getVersion(), '==') !== 0) {
            $newPlugin->version = $manifest->getVersion();
        }
        
        static::updateMetadataFromManifest($newPlugin, $manifest);

        // Avoid bumping updated_at when nothing actually changed.
        if($isCreation || $newPlugin->isDirty()) {
            $newPlugin->save();
        }

        return $newPlugin;
    }
    
    
    public static function updateMetadataFromManifest(Plugin $plugin, PluginManifest $manifest): void {
        $manifestAuthors = $manifest->getAuthors() ?? [];
        $manifestDescription = $manifest->getDescription() ?? "";
        $manifestLicence = $manifest->getLicence() ?? "";

        $metadata = $plugin->metadata ?? [];
        
        // Either the array key does not exist at all or when it's null, we provide a default value.
        $authors = (isset($metadata['authors']) ? $metadata['authors'] : []) ?? [];
        $description = (isset($metadata['description']) ? $metadata['description'] : "") ?? "";
        $licence = (isset($metadata['licence']) ? $metadata['licence'] : "") ?? "";

        
        // We only want to update the metadata if it has actually changed.
        if(
            $manifestAuthors !== $authors ||
            $manifestDescription !== $description ||
            $manifestLicence !== $licence
        ) {
            $plugin->metadata = [
                'authors' => $authors,
                'description' => $description,
                'licence' => $licence,
            ];
        }
    }
}
