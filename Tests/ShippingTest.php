<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Safecrow;
use Safecrow\App;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    private
        $shipping
    ;
    
    /**
     * @before
     */
    public function createApp()
    {
        $app = new App();
    
        $user = $app->getUsers()->getByEmail("test596@test.ru");
        $orders = $app->getOrders($user['id']);
        $ordersList = $orders->getList();
    
        $this->shipping = $orders->getShipping($ordersList[0]['id']);
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/shipping.test.log', Logger::INFO));
    }
    
    
}