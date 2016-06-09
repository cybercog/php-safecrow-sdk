<?
namespace Safecrow\Tests;

use Safecrow\App;
use Safecrow\Config;

class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createDevApp()
    {
        $host = Config::ENVIROMENT == "dev" ? Config::DEV_HOST : Config::PROD_HOST;
        
        $app = new App();
        
        $this->assertEquals($host, $app->getHost());
    }
   
    /**
     * @test
     */
    public function testAllowedFiles()
    {
        $app = new App();
        
        $this->assertTrue(App::IsAllowedFileType("text/plain"));
        $this->assertFalse(App::IsAllowedFileType("text/xml"));
    }
}
