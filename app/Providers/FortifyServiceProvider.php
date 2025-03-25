<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {
            if(!$request->has('email') && !$request->has('password')) {
                return false;
            }

            $username = $request->get('email');
            if(Str::contains($username, '@')) {
                $user = User::where('email', $request->get('email'))->withoutTrashed()->first();
            } else {
                $user = User::where('nickname', $request->get('nickname'))->withoutTrashed()->first();
            }
            if(!isset($user)) {
                Sleep::for(2)->seconds();
                return false;
            }
            if($user->login_attempts === 0) {
                return false;
            }

            if(!Hash::check($request->password, $user->password)) {
                return false;
            }

            if($user->login_attempts > 0) {
                $user->login_attempts--;
                $user->save();
            }
            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(50)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(50)->by($request->session()->get('login.id'));
        });
    }
}
