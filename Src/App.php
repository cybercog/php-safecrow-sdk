<?
namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Exceptions\AuthException;

class App
{
    private 
        $sysClient,
        $usrClient,
        $key,
        $secret
    ;
    
    public function __construct()
    {
        $this->key = Config::API_KEY;
        $this->secret = Config::API_SECRET;
        
        $this->sysClient = new Client($this->getKey(), $this->getSecret(), $this->getHost());

        $this->usrClient = clone $this->sysClient;
        $this->usrClient->useUserRequests();
        
        $this->users = new Users($this->sysClient);
        $this->orders = new Orders($this->usrClient, $this->users);
    }
    
    public function getUsers()
    {
        return new Users($this->sysClient);
    }
    
    public function getSubscriptions()
    {
        return new Subscriptions($this->sysClient);
    }
    
    public function getOrders($userId)
    {
        if(!$this->getUsers()->getUserToken($userId)) {
            throw new AuthException();
        }
        
        return new Orders($this->usrClient, $userId);
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