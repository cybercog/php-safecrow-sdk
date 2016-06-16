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
        $logger
    ;
    
    private
        $payments,
        $supplierPayments
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
        
        foreach ($ordersList as $order) {
            if($order['role'] == Payers::CONSUMER) {
                $this->payments = $orders->getPayments($order['id']);
            }
            else {
                $this->supplierPayments = $orders->getPayments($order['id']);
            }
        }
    
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
        $res = $this->payments->getInfo();
        
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
        $res = $this->supplierPayments->createBill("Ivanov Ivan");
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
        $res = $this->payments->createBill("Ivanov Ivan");
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
        $res = $this->payments->getBill();
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
        $res = $this->payments->downloadInvoice();
        self::$logger->info($res);
        $this->assertInternalType('string',$res);
    }
}