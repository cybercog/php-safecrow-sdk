<?
namespace Safecrow\Http;

class Client
{
    private static 
        $arAllowedActions = array('post', 'get', 'put', 'delete', 'patch', 'options', 'head');
    
    private 
        $key,
        $secret,
        $host,
        $bSystemRequests = true
    ;
    
    public function __construct($apiKey, $apiSecret, $host)
    {
        $this->key = $apiKey;
        $this->secret = $apiSecret;
        $this->host = $host;
    }
    
    public function getRequest($method, $url, $data, &$status = null)
    {
        $data = empty($data) ? array() : $data;
        $data = array_merge($data, $this->getCredentials());
        
        $query = new Query($method, $this->host . "/" . trim($url, "/"));
        $query->setPostData($data);
        
        $res = $query->exec();
        $status = $query->isSuccess();
        
        return $res;
    }
    
    public function __call($method, $args)
    {
        $method = strtolower($method);
        if(!in_array($method, self::$arAllowedActions)) {
            throw new Exception('Method is not allowed');
        }
        
        for($i=0; $i <3 ; $i++) {
            if(!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        
        return $this->getRequest($method, $args[0], $args[1], $args[2]);
    }
    
    public function useUserRequests()
    {
        $this->bSystemRequests = false;
    }
    
    private function isUserRequests()
    {
        return !$this->bSystemRequests;
    }
    
    private function getCredentials()
    {
        $arSystem = array(
            'api_key' => $this->key,
            'secret' => $this->secret,
            'request_time' => date('c')
        );
        
        $arUser = array(
            'access_token' => isset($_SESSION['safecrow_access_token']) ? $_SESSION['safecrow_access_token'] : "" 
        );
        
        return $this->isUserRequests() ? $arUser : $arSystem;
    }
}