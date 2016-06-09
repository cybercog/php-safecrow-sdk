<?
namespace Safecrow\Tests;

use Safecrow;
use Safecrow\App;
use Safecrow\Users;
use Safecrow\Exceptions\RegistrationException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Safecrow\Config;

class UserTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger,
        $userName,
        $userEmail,
        $userPhone
    ;
    
    private 
        $app,
        $users,
        $userWithPhone,
        $userWithEmail
    ;
    
    /**
     * @before
     */
    public function createApp()
    {
        self::$userName = "test". rand(0, 10000);
        self::$userEmail = self::$userName."@test.ru";
        self::$userPhone = "8".rand(9000000000, 9999999999);
        
        $this->app = new App(Config::API_KEY, Config::API_SECRET, true);
        $this->users = new Users($this->app);
        
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/user.test.log', Logger::INFO));
    }

    /**
     * @test
     */
    public function regUserWithEmail()
    {
        $user = $this->users->reg([
            'name' => self::$userName,
            'email' => self::$userEmail,
            'accepts_conditions' => true
        ]);

        self::$logger->info(json_encode([
            'method' => __METHOD__,
            'data' => $user
        ]));
        
        $this->assertEquals($user['name'], self::$userName);
        $this->assertEquals($user['email'], self::$userEmail);
    }
    
    /**
     * @test
     */
    public function regUserWithPhone()
    {
        $user = $this->app->getUsers()->reg([
            'name' => self::$userName,
            'phone' => self::$userPhone,
            'accepts_conditions' => true
        ]);
        
        self::$logger->info(json_encode([
            'method' => __METHOD__,
            'data' => $user,
        ]));
        
        $this->assertEquals($user['name'], self::$userName);
        $this->assertEquals($user['phone'], self::$userPhone);
        
    }
    
    /**
     * @test
     */
    public function regUserWithoutEmailAndPhone()
    {
        $this->expectException(RegistrationException::class);
        $this->use$this->app->getUsers()           'accepts_conditions' => true
        ]);
    }
    
    /**
     * @test
     */
    public function regUserWithoutReqFields()
    {
        $this->expectException(RegistrationException::class);
        $this->app->getUsers()->reg([
            'name' => self::$userName
        ]);
    }
    
    /**
     * @test
     */
    public function authUnsuccess()
    {
        $res = $this->app->getUsers()->auth(1);
        $this->assertArrayHasKey("errors", $res);
    }
    
    /**
     * @test
     */
    public function authSuccess()
    {
        $res = $this->app->getUsers()->auth(406);
        $this->assertArrayHasKey("access_token",$res);
    }
    
    /**
     * test
     */
    public function getUserAccessToken()
    {
        $this->assertEquals($_SERVER['safecrow_access_token'], $this->app->getUsers()->getUserToken(406));
    }
    
    /**
     * @test
     */
    public function findUserByPhone()
    {
        $user = $this->app->getUsers()->getByPhone("89999216803");
        self::$logger->info(json_encode([
            'method' => __METHOD__,
            'data' => $user,
        ]));
        $this->assertEquals($user['phone'], "89999216803");
    }
    
    /**
     * @test
     */
    public function findUserByEmail()
    {
        $user = $this->app->getUsers()->getByEmail("test2220@test.ru");
        self::$logger->info(json_encode([
            'method' => __METHOD__,
            'data' => $user,
        ]));
        $this->assertEquals($user['email'], "test2220@test.ru");
    }
    
    /**
     * @test
     */
    public function searchUserByEmptyEmail()
    {
        $user = $this->app->getUsers()->getByEmail("");
        $this->assertFalse($user);
    }
    
    /**
     * @test
     */
    public function searchUserByEmptyPhone()
    {
        $user = $this->app->getUsers()->getByPhone("");
        $this->assertFalse($user);
    }
    
    /**
     * @test
     */
    public function searchUserByIncorrectEmail()
    {
        $user = $this->app->getUsers()->getByEmail("incorrect_email");
        $this->assertFalse($user);
    }
    
    /**
     * @test
     */
    public function searchUserByEmailFail()
    {
        $user = $this->app->getUsers()->getByEmail("durov@vk.com");
        
        $this->assertArrayHasKey("errors", $user);
    }
    
    /**
     * @test
     */
    public function searchUserByPhoneFail()
    {
        $user = $this->app->getUsers()->getByPhone("19001234567");
        
        $this->assertArrayHasKey("errors", $user);
    }
    
    /**
     * @test
     */
    public function getCurrentUser()
    {
        $user = $this->app->getUsers()->getCurrent();
        
        $this->assertNotEmpty($user);
    }
}