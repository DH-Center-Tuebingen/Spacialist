<?php

namespace App\AttributeTypes;

use App\User;
use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserlistAttribute extends AttributeBase
{
    protected static string $type = "userlist";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $nicknames = explode(';', $data);
        $list = [];
        $incorrectUsers = [];
        foreach($nicknames as $name) {
            $name = trim($name);
            if($name === '') {
                continue;
            }

            try {
                //TODO: The deleted_at check should be necessary here!!!!
                $user = User::where('nickname', $name)
                    ->where("deleted_at", null)
                    ->firstOrFail();
                    
                $list[] = $user->id;
            } catch(ModelNotFoundException $e) {
                $incorrectUsers[] = $name;
            }
        }
        
        if(count($incorrectUsers) > 0){
            throw new InvalidDataException("The following users were not found: " . implode(', ', $incorrectUsers));
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
