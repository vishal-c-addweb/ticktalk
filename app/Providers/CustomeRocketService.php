<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Providers\CustomeRocketService;



class CustomeRocketService extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {      
        $this->bootRocketSocialite();
    }       
    public  function bootRocketSocialite()
    {
        try {
            $getAuthData = DB::table('social_auth_settings')->select('rocket_chat_client_id','rocket_chat_secret_secret_id','rocket_chat_redirect_uri','rocket_chat_server_uri','rocket_chat_status')->where('id',1)->first();
            if($getAuthData){
               $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
               $socialite->extend(
                   'rocket',
                   function ($app) use ($socialite , $getAuthData) {
                       $config=[
                           "client_id" => $getAuthData->rocket_chat_client_id,
                           "client_secret" => $getAuthData->rocket_chat_secret_secret_id,
                           "redirect" => $getAuthData->rocket_chat_redirect_uri,
       
                       ];
                      
                       return $socialite->buildProvider(CustomRocketChatProvider::class, $config);
                   }
               );
            }

        } catch (\Throwable $th) {
            
        } 
    }
    

}
