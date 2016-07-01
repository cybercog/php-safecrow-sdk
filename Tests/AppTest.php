<?php
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
        $app = new App(new Config("dev"));
        
        var_dump($app->getHost());
        $this->assertEquals(Config::DEV_HOST, $app->getHost());
    }
    
    /**
     * Получение экземпляра класса для работы с пользователями
     * 
     * @test
     * @covers App::getUsers()
     */
    public function getUsersObject()
    {
        $app = new App(new Config("dev"));
        $this->assertInstanceOf(Users::class, $app->getUsers());
    }
    
    /**
     * Получение экземпляра класса для работы с заказами
     * @test
     * @covers App::getOrders($userId)
     */
    public function getOrdersObject()
    {
        $userName = "test". rand(0, 10000);
        $userEmail = $userName."@test.ru";
        
        $app = new App(new Config("dev"));
        $user = $app->getUsers()->reg(array(
            'name' => $userName,
            'email' => $userEmail,
            'accepts_conditions' => true
        ));

        $this->assertInstanceOf(Orders::class, $app->getOrders($user['id']));
    }
    
    /**
     * Ошибка при получение экземпляра класса для работы с пользователями
     * @test
     * @covers App::getOrders($userId)
     * @expectedException Safecrow\Exceptions\AuthException
     */
    public function getOrdersWithoutUserId()
    {
        $app = new App(new Config("dev"));
        $app->getOrders(null);
    }
    
    /**
     * Получение экземпляра класса для работы с подписками
     * @test
     * @covers App::getSubscriptions()
     */
    public function getSubscriptions()
    {
        $app = new App(new Config("dev"));
        $this->assertInstanceOf(Subscriptions::class, $app->getSubscriptions());
    }
   
    /**
     * Проверка корректности типа файла
     * 
     * @test
     */
    public function testAllowedFiles()
    {
        $app = new App(new Config("dev"));
        
        $this->assertTrue(App::IsAllowedFileType("text/plain"));
        $this->assertFalse(App::IsAllowedFileType("text/xml"));
    }
}