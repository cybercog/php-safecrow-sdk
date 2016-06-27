<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Safecrow;
use Safecrow\App;
use Safecrow\Enum\PayerTypes;
use Safecrow\Enum\Payers;

class PaymetsTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger,
        $payments,
        $supplierPayments
    ;
    
    /**
     * @beforeClass
     */
    public static function createApp()
    {
        $app = new App();
        
        $userName = "test". rand(0, 10000);
        $curUser = $app->getUsers()->reg(array(
            'name' => $userName,
            'email' => $userName."@test.ru",
            'accepts_conditions' => true
        ));
        
        $userName = "test". rand(0, 10000);
        $otherUser = $app->getUsers()->reg(array(
            'name' => $userName,
            'email' => $userName."@test.ru",
            'accepts_conditions' => true
        ));
        
        $orders = $app->getOrders($curUser['id']);
        $supOrder = $orders->create(array(
            'title' => 'Order test #'.rand(1,9999),
            'order_description' => 'order description',
            'cost' => rand(10000, 100000),
            'commission_payer' => Payers::CONSUMER
        ));
        
        $conOrder = $orders->create(array(
            'title' => 'Order test #'.rand(1,9999),
            'order_description' => 'order description',
            'cost' => rand(10000, 100000),
            'supplier_id' => $otherUser['id'],
            'consumer_id' => $curUser['id'],
            'commission_payer' => Payers::FIFTY_FIFTY
        ));

        self::$payments = $orders->getPayments($conOrder['id']);
        self::$supplierPayments = $orders->getPayments($supOrder['id']);
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/payments.test.log', Logger::INFO));
    }
    
    /**
     * Получение информации об оплате
     * 
     * @test
     * @covers Payments::getInfo
     */
    public function getInfo()
    {
        $res = self::$payments->getInfo();
        
        $this->assertArrayHasKey('consumer_pay', $res);
    }
    
    /**
     * Неудачная попытка создания счета из-за некорректного статуса заказа
     * 
     * @test
     * @covers Payments::createBill
     */
    public function failCreateBill()
    {
        $res = self::$supplierPayments->createBill("Ivanov Ivan");
        $this->assertArrayHasKey('errors', $res);
    }
    
    /**
     * Создание счета
     * 
     * @test
     * @covers Payments::createBill
     */
    public function createBill()
    {
        $res = self::$payments->createBill("Ivanov Ivan");
        $this->assertNotEmpty($res['id']);
        
        return $res;
    }
    
    /**
     * Получение счета
     * 
     * @test
     * @covers Payments::getBill
     * @depends createBill
     */
    public function getBill($bill)
    {
        $res = self::$payments->getBill();
        $this->assertEquals($res['id'], $bill['id']);
    }
    
    /**
     * Получение ссылки на счет
     * 
     * @test
     * @covers Payments::downloadInvoice
     */
    public function downloadInvoice()
    {
        $res = self::$payments->downloadInvoice();
        self::$logger->info($res);
        $this->assertInternalType('string',$res);
    }
}