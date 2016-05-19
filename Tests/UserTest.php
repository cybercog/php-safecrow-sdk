<?
namespace Safecrow\Tests;

use Safecrow;
use Safecrow\Exceptions\RegistrationException;

class UserTest extends \PHPUnit_Framework_TestCase
{
    private static
        $key = "b9598ffa-f905-4908-92b1-90e602baa2b2",
        $secret = "a6420c86bdd9fe871315210e13eed817fc88de887b9ebe953edfae46174c9434",
        
        $dev = "http://dev.safecrow.ru/api/v1",
        $prod = "https://www.safecrow.ru/api/v1",
        
        $userName = "Виданов Алексей",
        $userEmail = "aleksey.vidanov@mail.ru",
        $userPhone = "+7(916) 140-25-26"
    ;
    
    public function testUserRegister()
    {
        //reg user with email
        Users::register([
            'name' => self::$userName,
            'email' => self::$userEmail,
            'accepts_conditions' => true
        ]);
        
        $this->assertEquals($user['name'], self::$userName);
        $this->assertEquals($user['email'], self::$userEmail);
        
        //reg user with phone
        Users::register([
            'name' => self::$userName,
            'phone' => self::$userPhone,
            'accepts_conditions' => true
        ]);
        
        $this->assertEquals($user['name'], self::$userName);
        $this->assertEquals($user['phone'], self::$userPhone);
        
        //fail reg
        $this->expectException(RegistrationException::class);
        Users::register([
            'accepts_conditions' => true
        ]);
    }
}