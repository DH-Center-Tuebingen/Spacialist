<?php

namespace App\Console\Commands;

use App\User;
use App\Role;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateModels extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create {--r|role : Whether a role should be created} {--u|user : Whether a user should be created (default)} {name=admin : Name of the new user/role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cli tool to add new users or roles in Spacialist';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $type = '';
        $data = [];
        $data['name'] = $this->argument('name');
        $isRole = $this->option('role');
        $isUser = $this->option('user');
        if ($isRole && $isUser) {
            $this->error('Can not create user and role simultaneously!');
            return 1;
        }
        if ($isRole) {
            $type = 'role';
        } else { // otherwise default to user
            $type = 'user';
        }
        if (!isset($data['name']) || $data['name'] == 'admin') {
            $data['name'] = $this->anticipate("Please provide the new $type name", ['admin']);
        }
        if ($isRole) {
            $data['display_name'] = $this->anticipate("Please provide the desired display name", [Str::slug($data['name'])]);
            $data['description'] = $this->ask("Please provide the desired description");

            $validator = Validator::make($data, [
                'name' => 'alpha_dash|unique:roles,name',
            ]);
        } else {
            $data['nickname'] = $this->anticipate("Please enter your nick name", [
                Str::lower(Str::before($data['name'], ' '))
            ]);
            $data['email'] = Str::lower($this->ask("Please enter your email address"));
            $data['password'] = Hash::make($this->secret("Please enter your password"));

            $validator = Validator::make($data, [
                'email' => 'email|unique:users,email',
                'nickname' => 'alpha_dash|unique:users,nickname'
            ]);
        }

        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $e) {
                $this->error($e);
            }
            return 1;
        }

        $confirmStr = "Is this correct?\n";

        foreach ($data as $k => $d) {
            if ($k == 'password') continue;
            $confirmStr .= "\t" . Str::studly($k) . ": $d\n";
        }

        if ($this->confirm($confirmStr, true)) {
            if ($isRole) {
                $role = new Role();
                $role->name = $data['name'];
                $role->display_name = $data['display_name'];
                $role->description = $data['description'];
                $role->guard_name = 'web';
                $role->save();

                $this->info("Role $role->name created!");
                return 0;
            } else {
                $user = new User();
                $user->name = $data['name'];
                $user->nickname = $data['nickname'];
                $user->email = $data['email'];
                $user->password = $data['password'];
                $user->save();

                $this->info("User $user->name created!");
                return 0;
            }
        } else {
            $this->line('Data not confirmed. Aborted.');
            return 0;
        }
    }
}
