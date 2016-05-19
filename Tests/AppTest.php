<?
namespace Safecrow\Tests;

use Safecrow\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private static 
        $key = "b9598ffa-f905-4908-92b1-90e602baa2b2",
        $secret = "a6420c86bdd9fe871315210e13eed817fc88de887b9ebe953edfae46174c9434",
        
        $dev = "http://dev.safecrow.ru/api/v1",
        $prod = "https://www.safecrow.ru/api/v1"
    ;
    
    public function testCreateAppInstance()
    {
        //Test dev 
        $app = new App(self::$key, self::$secret, true);
        
        $this->assertEquals(self::$key, $app->getKey());
        $this->assertEquals(self::$secret, $app->getSecret());
        $this->assertEquals(self::$dev, $app->getHost());
        
        //Test prod
        $app = new App(self::$key, self::$secret);
        
        $this->assertEquals(self::$prod, $app->getHost());
    }
    
    public function testAllowedFiles()
    {
        $app = new App(self::$key, self::$secret, true);
        
        $this->assertTrue($app->IsAllowedFileType("text/plain"));
        $this->assertFalse($app->IsAllowedFileType("text/xml"));
    }
}
