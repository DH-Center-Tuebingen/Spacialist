<?php

namespace App;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $appends = [
        'info',
    ];

    public function getInfoAttribute() {
        if(!isset($this->data['resource'])) return [];
        if($this->type == 'App\Notifications\CommentPosted') {
            $skip = false;
            switch($this->data['resource']['type']) {
                case 'App\Entity':
                    try {
                        $name = Entity::findOrFail($this->data['resource']['id'])->name;
                    } catch(ModelNotFoundException $e) {
                        $skip = true;
                    }
                    break;
                case 'App\AttributeValue':
                case 'attribute_values':
                    try {
                        $name = Entity::findOrFail($this->data['resource']['meta']['entity_id'])->name;
                        $attrUrl = Attribute::findOrFail($this->data['resource']['meta']['attribute_id'])->thesaurus_url;
                    } catch(ModelNotFoundException $e) {
                        $skip = true;
                    }
                    break;
                default:
                    $skip = true;
                    break;
            }
            if(!$skip) {
                $data = [
                    'name' => $name,
                ];
                if(isset($attrUrl)) {
                    $data['attribute_url'] = $attrUrl;
                }
                return $data;
            }
        } else if($this->type == 'App\Notifications\EntityUpdated') {
            try {
                return [
                    'name' => Entity::findOrFail($this->data['resource']['id'])->name,
                ];
            } catch(ModelNotFoundException $e) {
            }
        }
        return [];
    }
}
