<?php
namespace App\Providers;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Illuminate\Support\Facades\DB;

class CustomRocketChatProvider extends AbstractProvider implements ProviderInterface 
{

    /**
     * {@inheritdoc}
     */
    public function getAuthSettings()
    { 
        $serveruri = DB::table('social_auth_settings')->select('rocket_chat_server_uri')->where('id',1)->first();
        return $serveruri;
    }
    protected function getAuthUrl($state)
    { 
        $serveruri = $this->getAuthSettings();
        return $this->buildAuthUrlFromBase($serveruri->rocket_chat_server_uri.'/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        $serveruri = $this->getAuthSettings();
        return $serveruri->rocket_chat_server_uri.'/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Authorization' => 'Basic ' .$this->clientId . ':' . $this->clientSecret, 'code'    => $this->getTokenFields($code)],
            
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $serveruri = $this->getAuthSettings();
        $response = $this->getHttpClient()->get($serveruri->rocket_chat_server_uri.'/api/v1/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
        // return implode(' ', $scopes);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'username' => $user['username'],
            'name'     => $user['name'],
        ]);
    }
}