<?php

namespace App\Http\Controllers\Custom;
use App\Http\Controllers\Controller;
use App\Models\Social;
use App\Models\User;
use App\Traits\SocialAuthSettings;
use Exception;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomLoginController extends Controller
{
    use AppBoot, SocialAuthSettings;

    protected $redirectTo = 'account/dashboard';

  
    public function loginToRocket()  
    {   
        try {
            return Socialite::with('rocket')->scopes(['read',])->redirect();
        } catch (\Throwable $th) {
            return redirect()->to('login')->withErrors("please enter valid rocket chat credentials");
        }
    }
    public function handleLoginRocketCallback(Request $request){
        try {
            $user = Socialite::with('rocket')->user();
            if (isset($user->user['email'])) {
                $email = $user->user['email'];
                $auth = User::Where('email','=',$email)->first();
            }
            else{
                return redirect()->to('login')->withErrors("Please verify your email.");
            }
            if(!empty($auth))
            {
                  $user_id = User::where('id', '=', $auth->id)->first();
                    User::where('id', '=', $auth->id)->update([
                                'rocket_uid' => $user->user['_id'],
                                'rocket_token'=> $user->token,
                                'rocket_refresh_token'=> $user->refreshToken,
                                'rocket_username' => $user->user['username'],
                                'status' => 'active',
                                 'login' => 'enable'
                            ]);  
                $active = $user->user['active'];
                if($active){
                    Auth::loginUsingId($auth->id);
                    return redirect()->to('account/dashboard');
                }
                else{
                    return redirect()->to('login')->withErrors("Deactivated User.");
                }
            }else{
                return redirect()->to('login')->withErrors("These credentials do not match our records.");
            }
        } catch (\Throwable $th) {
            return redirect()->to('login')->withErrors("please enter valid rocket chat credentials");
        }
    }

}
