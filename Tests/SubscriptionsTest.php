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
        $app = new App();
    
        $this->subscriptions = $app->getSubscriptions();
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/subscriptions.test.log', Logger::INFO));
    }
    
    /**
     * �������� ������� ��������
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
     * ��������
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
     * ��������� ������ ��������
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
     * ������������� ��������
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
     * �������� ��������
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