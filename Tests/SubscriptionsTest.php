<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Safecrow;
use Safecrow\App;
use Safecrow\Config;

class SubscriptionsTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    private
        $subscriptions
    ;
    
    /**
     * @before
     */
    public function createApp()
    {
        $app = new App(new Config("dev"));
    
        $this->subscriptions = $app->getSubscriptions();
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/subscriptions.test.log', Logger::INFO));
    }
    
    /**
     * Неудчная попытка подписки
     * 
     * @test
     * @covers Subscriptions::subscribe
     * @expectedException Safecrow\Exceptions\SafecrowException
     */
    public function failSubscribe()
    {
        $res = $this->subscriptions->subscribe("http://safecrow.mgnexus.ru/subscription", array());
    }
    
    /**
     * Подписка
     * 
     * @test
     * @covers Subscriptions::subscribe
     */
    public function subscribe()
    {
        $res = $this->subscriptions->subscribe("http://safecrow.mgnexus.ru/subscription", array("paid"));
        
        $this->assertNotEmpty($res['subscription_id']);
        $this->assertEquals($res['confirmed'], false);
        
        return $res;
    }
    
    /**
     * Получение списка подписок
     * 
     * @test
     * @covers Subscriptions::getList
     * @depends subscribe
     */
    public function getList($sub)
    {
        $bFinded = false;
        $res = $this->subscriptions->getList();
        self::$logger->info(print_r($res,1));
        foreach ($res as $arSub) {
            if($arSub['subscription_id'] == $sub['subscription_id']) {
                $bFinded = true;
                break;
            }
        }
        
        $this->assertEquals($bFinded, true);
    }
    
    /**
     * Подтверждение подписки
     * 
     * @test
     * @covers Subscription::confirm
     * @depends subscribe
     */
    public function confirm($sub)
    {
        $res = $this->subscriptions->confirm($sub['subscription_id']);
        
        $this->assertEquals($res, true);
    }
    
    /**
     * Удаление подписки
     * 
     * @test
     * @covers Subscriptions::unsubscribe
     * @depends subscribe
     */
    public function unsubscribe($sub)
    {
        $res = $this->subscriptions->unsubscribe($sub['subscription_id']);
        $this->assertEquals($res, true);
    }
}