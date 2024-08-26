<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EntityTypeRelation extends Model {
    use LogsActivity;

    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'child_id'
    ];

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    public static function isAllowed(?int $parent, int $child): bool {
        if ($parent == null) {
            return EntityType::where('is_root', true)
                ->where('id', $child)
                ->exists();
        }

        return self::where('parent_id', $parent)
            ->where('child_id', $child)
            ->exists();
    }

    // TODO relations
}
