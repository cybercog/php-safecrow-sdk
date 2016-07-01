<?php
namespace Safecrow;

use Safecrow\Exceptions\RegistrationException;
use Safecrow\Exceptions\AuthException;
use Safecrow\Http\Client;

class Users
{
    private 
        $client,
        $token,
        $tokens = array()
    ;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * Регистрация пользователя
     * @param array $params
     * @return boolean|mixed
     * @throws RegistrationException
     */
    public function reg(array $params)
    {
        $this->validate($params);
        $res = $this->getClient()->post("/sessions/register_user", $params);
        
        return $res['user'] ?: $res;
    }
    
    /**
     * Авторизация пользователя
     * @param int $id
     * @return string
     * @throws AuthException
     */
    public function auth($id)
    {
        if(!(int)$id) {
            throw new AuthException("Некорректный id пользователя");
        }
        
        $res = $this->getClient()->post("/sessions/auth", array('user_id' => (int)$id));
        
        if(!empty($res['access_token'])) {
            $this->setLastTokenUpdate(time());
        }
        
        return $res;
    }
    
    /**
     * Поиск пользователя по Email
     * 
     * @param string $email
     * @return array|bool
     */
    public function getByEmail($email)
    {
        if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        $res = $this->getClient()->post("/sessions/find_user", array('email' => $email));
        
        return isset($res['user']) ? $res['user'] : $res;
    }
    
    /**
     * Поиск пользователя по телефону
     * 
     * @param string $phone
     * @return array|bool
     */
    public function getByPhone($phone)
    {
        if(empty($phone)) {
            return false;
        }
        
        $res = $this->getClient()->post("/sessions/find_user", array('phone' => $phone));

        return isset($res['user']) ? $res['user'] : $res;
    }
    
    public function getUserToken($userId)
    {
        if(!(int)$userId) {
            return false;
        }
        
        if(!isset($this->tokens[$userId]) || time() - Config::USER_TOKEN_LIFETIME - $this->getLastTokenUpdate() >= 0) {
            $res = $this->auth($userId);
            $this->tokens[$userId] = isset($res['access_token']) ? $res['access_token'] : false;
        }
        
        $this->setUserToken($this->tokens[$userId]);
    
        return $this->token;
    }
    
    private function setUserToken($token)
    {
        $this->token = $_SESSION["safecrow_access_token"] = $token;
    }
    
    private function getClient()
    {
        return $this->client;
    }
    
    private function validate($params)
    {
        if(empty($params['accepts_conditions'])) {
            throw new RegistrationException;
        }
        
        if(empty($params['email']) && empty($params['phone'])) {
            throw new RegistrationException;
        }
    }
    
    private function getLastTokenUpdate()
    {
        return isset($_SERVER['safecrow_token_updated']) ? $_SERVER['safecrow_token_updated'] : 0;
    }
    
    private function setLastTokenUpdate($val)
    {
        $_SERVER['safecrow_token_updated'] = $val;
    }
}