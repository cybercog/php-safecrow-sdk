<?
namespace Safecrow\Tests;

use Safecrow\App;
use Safecrow\Config;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private static 
        $dev = "http://dev.safecrow.ru/api/v1",
        $prod = "https://www.safecrow.ru/api/v1"
    ;
    
    /**
     * @test
     */
    public function createDevApp()
    {
        $app = new App(Config::API_KEY, Config::API_SECRET, true);
        
        $this->assertEquals(self::$dev, $app->getHost());
    }
    
    /**
     * @test
     */
    public function createProdApp()
    {
        $app = new App(Config::API_KEY, Config::API_SECRET);
        $this->assertEquals(self::$prod, $app->getHost());
    }
    
    /**
     * @test
     */
    public function checkState()
    {
        $app = new App(Config::API_KEY, Config::API_SECRET, true);
        
        $this->assertEquals(Config::API_KEY, $app->getKey());
        $this->assertEquals(hash("sha256", (Config::API_KEY.Config::API_SECRET.date('c'))), $app->getSecret());
    }
    
    /**
     * @test
     */
    public function testAllowedFiles()
    {
        $app = new App(Config::API_KEY, Config::API_SECRET, true);
        
        $this->assertTrue($app->IsAllowedFileType("text/plain"));
        $this->assertFalse($app->IsAllowedFileType("text/xml"));
    }
}
