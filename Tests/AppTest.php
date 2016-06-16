<?
namespace Safecrow\Tests;

use Safecrow\App;
use Safecrow\Config;
use Safecrow\Users;
use Safecrow\Orders;
use Safecrow\Subscriptions;

class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Корректность создания экземпляра класса приложения
     * 
     * @test
     * @covers App::getHost()
     */
    public function createApp()
    {
        $host = Config::ENVIROMENT == "dev" ? Config::DEV_HOST : Config::PROD_HOST;
        
        $app = new App();
        
        $this->assertEquals($host, $app->getHost());
    }
    
    /**
     * Получение экземпляра класса для работы с пользователями
     * 
     * @test
     * @covers App::getUsers()
     */
    public function getUsersObject()
    {
        $app = new App();
        $this->assertInstanceOf(Users::class, $app->getUsers());
    }
    
    /**
     * Получение экземпляра класса для работы с заказами
     * @test
     * @covers App::getOrders($userId)
     */
    public function getOrdersObject()
    {
        $app = new App();
        $this->assertInstanceOf(Orders::class, $app->getOrders(406));
    }
    
    /**
     * Ошибка при получение экземпляра класса для работы с пользователями
     * @test
     * @covers App::getOrders($userId)
     * @expectedException Safecrow\Exceptions\AuthException
     */
    public function getOrdersWithoutUserId()
    {
        $app = new App();
        $app->getOrders(null);
    }
    
    /**
     * Получение экземпляра класса для работы с подписками
     * @test
     * @covers App::getSubscriptions()
     */
    public function getSubscriptions()
    {
        $app = new App();
        $this->assertInstanceOf(Subscriptions::class, $app->getSubscriptions());
    }
   
    /**
     * Проверка корректности типа файла
     * 
     * @test
     */
    public function testAllowedFiles()
    {
        $app = new App();
        
        $this->assertTrue(App::IsAllowedFileType("text/plain"));
        $this->assertFalse(App::IsAllowedFileType("text/xml"));
    }
}
