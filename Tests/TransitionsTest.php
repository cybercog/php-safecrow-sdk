<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Safecrow;
use Safecrow\App;

class TransitionsTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    private
        $transitions
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
    
        $this->transitions = $orders->getTransitions($ordersList[0]['id']);
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/transitions.test.log', Logger::INFO));
    }
    
    /**
     * ѕолучение списка возможных переходов
     * 
     * @test
     * @covers Transitions::getList
     */
    public function getList()
    {
        $res = $this->transitions->getList();
        self::$logger->info(print_r($res,1));
    }
}