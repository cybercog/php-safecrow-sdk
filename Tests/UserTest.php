<?
namespace Safecrow\Tests;

use Safecrow;
use Safecrow\App;
use Safecrow\Users;
use Safecrow\Exceptions\RegistrationException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class UserTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger,
        $userName,
        $userEmail,
        $userPhone
    ;
    
    /**
     * @before
     */
    public function createApp()
    {
        self::$userName = "test". rand(0, 10000);
        self::$userEmail = self::$userName."@test.ru";
        self::$userPhone = "8".rand(9000000000, 9999999999);
        
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/user.test.log', Logger::INFO));
    }

    /**
     * Регистрация пользователя с email-ом
     * 
     * @test
     * @covers Users::reg
     */
    public function regUserWithEmail()
    {
        $app = new App();
        
        $user = $app->getUsers()->reg(array(
            'name' => self::$userName,
            'email' => self::$userEmail,
            'accepts_conditions' => true
        ));

        self::$logger->info(json_encode(array(
            'method' => __METHOD__,
            'data' => $user
        )));
        
        $this->assertEquals($user['name'], self::$userName);
        $this->assertEquals($user['email'], self::$userEmail);
        
        return $user;
    }
    
    /**
     * Регистрация пользователя с телефоном
     * 
     * @test
     * @covers Users::reg
     */
    public function regUserWithPhone()
    {
        $app = new App();
        
        $user = $app->getUsers()->reg(array(
            'name' => self::$userName,
            'phone' => self::$userPhone,
            'accepts_conditions' => true
        ));
        
        self::$logger->info(json_encode(array(
            'method' => __METHOD__,
            'data' => $user,
        )));
        
        $this->assertEquals($user['name'], self::$userName);
        $this->assertEquals($user['phone'], self::$userPhone);
        
        return $user;
    }
    
    /**
     * Попытка регистрация пользователя без email и телефона
     * 
     * @test
     * @covers Users::reg
     * @expectedException Safecrow\Exceptions\RegistrationException
     */
    public function regUserWithoutEmailAndPhone()
    {
        $app = new App();
        
        $app->getUsers()->reg(array(
            'accepts_conditions' => true
        ));
    }
    
    /**
     * Попытка регистрации без согласия с условиями
     * 
     * @test
     * @covers Users::reg
     * @expectedException Safecrow\Exceptions\RegistrationException
     */
    public function regUserWithoutReqFields()
    {
        $app = new App();
        
        $app->getUsers()->reg(array(
            'name' => self::$userName
        ));
    }
    
    /**
     * Неудачная попытка авторизации
     * 
     * @test
     * @covers Users::auth
     */
    public function authUnsuccess()
    {
        $app = new App();
        
        $res = $app->getUsers()->auth(1);
        $this->assertArrayHasKey("errors", $res);
    }
    
    /**
     * Удачная попытка авторизации
     * 
     * @test
     * @covers Users::auth
     * @depends regUserWithEmail
     */
    public function authSuccess($user)
    {
        $app = new App();
        
        $res = $app->getUsers()->auth($user['id']);
        $this->assertArrayHasKey("access_token",$res);
        
        $res['user'] = $user;
        
        return $res;
    }
    
    /**
     * Получение access_token
     * 
     * @test
     * @covers Users::getUserToken()
     * @depends authSuccess
     */
    public function getUserAccessToken($data)
    {
        $app = new App(); 
        $this->assertNotEmpty($app->getUsers()->getUserToken($data['user']['id']));
    }
    
    /**
     * Поиск пользователя по телефону
     * 
     * @test
     * @covers Users::getByPhone
     * @depends regUserWithPhone
     */
    public function findUserByPhone($user)
    {
        $app = new App();
        
        $finded = $app->getUsers()->getByPhone($user['phone']);
        
        self::$logger->info(json_encode([
            'method' => __METHOD__,
            'data' => $user,
        ]));
        
        $this->assertEquals($finded['phone'], $user['phone']);
    }
    
    /**
     * Поиск пользователя по email
     * 
     * @test
     * @covers Users::getByEmail
     * @depends regUserWithEmail
     */
    public function findUserByEmail($user)
    {
        $app = new App();
        
        $finded = $app->getUsers()->getByEmail($user['email']);
        
        self::$logger->info(json_encode([
            'method' => __METHOD__,
            'data' => $user,
        ]));
        
        $this->assertEquals($finded['email'], $user['email']);
    }
    
    /**
     * Поиск по пустому email
     * 
     * @test
     * @covers Users::getByEmail
     */
    public function searchUserByEmptyEmail()
    {
        $app = new App();
        
        $user = $app->getUsers()->getByEmail("");
        $this->assertFalse($user);
    }
    
    /**
     * Поиск по пустому телефону
     * 
     * @test
     * @covers Users::getByPhone
     */
    public function searchUserByEmptyPhone()
    {
        $app = new App();
        
        $user = $app->getUsers()->getByPhone("");
        $this->assertFalse($user);
    }
    
    /**
     * Поиск по некорректному email
     * 
     * @test
     * @covers Users::getByEmail
     */
    public function searchUserByIncorrectEmail()
    {
        $app = new App();
        
        $user = $app->getUsers()->getByEmail("incorrect_email");
        $this->assertFalse($user);
    }
    
    /**
     * Попытка поиска по несуществующему email
     * 
     * @test
     * @covers Users::getByEmail
     */
    public function searchUserByEmailFail()
    {
        $app = new App();
        
        $user = $app->getUsers()->getByEmail("durov@vk.com");
        $this->assertArrayHasKey("errors", $user);
    }
    
    /**
     * Попытка поиска по несуществующему телефону
     * 
     * @test
     * @covers Users::getByPhone
     */
    public function searchUserByPhoneFail()
    {
        $app = new App();
        
        $user = $app->getUsers()->getByPhone("19001234567");
        $this->assertArrayHasKey("errors", $user);
    }
}