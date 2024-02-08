<?php

namespace App\AttributeTypes;

use App\User;
use App\Exceptions\InvalidDataException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserlistAttribute extends AttributeBase
{
    protected static string $type = "userlist";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        $nicknames = explode(';', $data);
        $list = [];
        foreach($nicknames as $name) {
            try {
                $name = trim($name);
                $user = User::where('nickname', $name)->firstOrFail();
                $list[] = $user->id;
            } catch(ModelNotFoundException $e) {
                throw new InvalidDataException("$name does not match any user's nickname");
            }
        }
        return json_encode($list);
    }

    public static function unserialize(mixed $data) : mixed {
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
