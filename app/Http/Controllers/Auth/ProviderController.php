<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\Catch_;

class ProviderController extends Controller
{
    public function redirect($provider){
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider){
        try{
            $SocialUser = Socialite::driver($provider)->user();
            if(User::where('email', $SocialUser->getEmail())->exists()){
                return redirect('/login')->withErrors(['email' =>'This email uses different method to login.']);
            }
            $user = User::where([
                'provider' => $provider,
                'provider_id' =>$SocialUser->id
            ])->first();

            if(!$user){
                $user = User::updateOrCreate([
                    'provider_id' => $SocialUser->id,
                    'provider' => $provider
                ], [
                    'name' => $SocialUser->name,
                    'username' => User::generateUserName($SocialUser->nickname),
                    'email' => $SocialUser->email,
                    'provider_token' => $SocialUser->token,
                    'provider_refresh_token' => $SocialUser->refreshToken,
                    'email_verified_at' => now(),
                ]);
            }
            
         
            Auth::login($user);
         
            return redirect('/dashboard');
        }catch(Exception $e){
            return redirect('/login');
        }
        //dd($SocialUser);
    }
}
