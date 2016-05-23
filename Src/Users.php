<?
namespace Safecrow;

use Safecrow\Exceptions\RegistrationException;
use Safecrow\Http\Query;
use Safecrow\Exceptions\AuthException;

class Users
{
    private 
        $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
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
        
        $query = new Query($this->app, "POST", "/sessions/register_user");
        $query->setPostData($params);
        
        $res = $query->exec();
        return $query->isSuccess() ? $res['user'] : $res;
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
        
        $query = new Query($this->app, "POST", "/sessions/auth");
        $query->setPostData(['user_id' => (int)$id]);
        
        $res = $query->exec();
        
        if($query->isSuccess()) {
            $_SESSION['safecrow_access_token'] = $res['access_token'];
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
        
        $query = new Query($this->app, "POST", "/sessions/find_user");
        $query->setPostData(['email' => $email]);
        
        $res = $query->exec();
        return $query->isSuccess() ? $res['user'] : $res;
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
        
        $query = new Query($this->app, "POST", "/sessions/find_user");
        $query->setPostData(['phone' => $phone]);
        
        $res = $query->exec();
        return $query->isSuccess() ? $res['user'] : $res;
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
}