<?
namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Exceptions\AuthException;

class App
{
    private 
        $client,
        $key,
        $secret
    ;
    
    public function __construct()
    {
        $this->key = Config::API_KEY;
        $this->secret = Config::API_SECRET;
        
        $this->client = new Client($this->getKey(), $this->getSecret(), $this->getHost());
        
        $oUserClient = clone $oSystemClient;
        $oUserClient->useUserRequests();
        
        $this->users = new Users($oSystemClient);
        $this->orders = new Orders($oUserClient, $this->users);
    }
    
    public function getUsers()
    {
        return new Users($this->client);
    }
    
    public function getOrders($userId)
    {
        $client = clone $this->client;
        $client->useUserRequests();
        
        if(!$this->getUsers()->getUserToken($userId)) {
            throw new AuthException();
        }
        
        return new Orders($client, $userId);
    }
    
    private function getKey()
    {
        return $this->key;
    }
    
    private function getSecret()
    {
        return hash("sha256", $this->key.$this->secret.date('c'));
    }
    
    public function getHost()
    {
        return Config::ENVIROMENT == "dev" ? Config::DEV_HOST : Config::PROD_HOST;
    }
    
    public static function IsAllowedFileType($type)
    {
        foreach (Config::ALLOWED_FILE_TYPES as $group) {
            if(in_array($type, $group)) {
                return true;
            }
        }
        
        return false;
    }
}