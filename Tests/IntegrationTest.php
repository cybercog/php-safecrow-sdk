<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Safecrow\App;
use Safecrow\Enum\Payers;
use Safecrow\Enum\PayerTypes;
use Safecrow\Enum\PaymentTypes;
use Safecrow\Enum\ClaimReasons;

/**
 * @backupGlobals
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    /**
     * @before
     */
    public function createApp()
    {
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/integration.test.log', Logger::INFO));
    }
    
    /**
     * регистрация 2 юзеров -> создание сделки -> подтверждение и оплата -> отправка -> прием
     * 
     * @test
     */
    public function successDeal()
    {
        $app = new App();
        
        //Создание пользователей
        $supplier = $app->getUsers()->reg(array(
            'name' => "supplier",
            'email' => "supplier".rand(0,1000)."@test.ru",
            'accepts_conditions' => true
        ));
        self::$logger->info(print_r($supplier,1));
        
        $consumer = $app->getUsers()->reg(array(
            'name' => "consumer",
            'email' => "consumer".rand(0,1000)."@test.ru",
            'accepts_conditions' => true
        ));
        self::$logger->info(print_r($consumer,1));
        
        $this->assertNotEmpty($supplier['id']);
        $this->assertNotEmpty($consumer['id']);
        
        //Создание сделки
        $order = $app->getOrders($supplier['id'])->create(array(
            'title' => 'Order test #'.rand(1,9999),
            'order_description' => 'order description',
            'cost' => rand(10000, 100000),
            'supplier_id' => $supplier['id'],
            'consumer_id' => $consumer['id'],
            'commission_payer' => Payers::FIFTY_FIFTY
        ));
        self::$logger->info(print_r($order,1));
        
        $this->assertNotEmpty($order['id']);
        
        //Платежная информация
        $supplierBillingInfo = $app->getOrders($supplier['id'])->getBilling($order['id'])->create(array(
            'holder_type' => PayerTypes::PERSONAL,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'name' => 'name lastname',
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        ));
        self::$logger->info(print_r($supplierBillingInfo,1));
        $this->assertNotEmpty($supplierBillingInfo['id']);
        
        $consumerBillingInfo = $app->getOrders($consumer['id'])->getBilling($order['id'])->create(array(
            'holder_type' => PayerTypes::PERSONAL,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'name' => 'name lastname',
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        ));
        self::$logger->info(print_r($consumerBillingInfo,1));
        $this->assertNotEmpty($consumerBillingInfo['id']);
        
        //Подтверждение и оплата
        $bill = $app->getOrders($consumer['id'])->getPayments($order['id'])->createBill("Ivanov Ivan");
        self::$logger->info(print_r($bill,1));
        $this->assertNotEmpty($bill['id']);

        $transitionState = $app->getOrders($consumer['id'])->getTransitions($order['id'])->doTransition("payment_verification");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Ждем пока переведется статус
        while(true)
        {
            sleep(120);
            $order = $app->getOrders($consumer['id'])->getByID($order[id]);
            if($order['state'] == 'paid') {
                break;
            }
        }
        
        //Создание доставки
        $shipping = $app->getOrders($supplier['id'])->getShipping($order['id'])->create(array(
            "company" => "Delivery Club"
        ));
        self::$logger->info(print_r($shipping,1));
        $this->assertNotEmpty($shipping['id']);
        
        //Передаем в доставку
        $transitionState = $app->getOrders($supplier['id'])->getTransitions($order['id'])->doTransition("shipping");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Отправляем
        $transitionState = $app->getOrders($supplier['id'])->getTransitions($order['id'])->doTransition("received");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Подтверждаем
        $transitionState = $app->getOrders($consumer['id'])->getTransitions($order['id'])->doTransition("received");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
    }
    
    /**
     * регистрация 2 юзеров -> создание сделки -> подтверждение и оплата -> отправка -> жалоба -> запрос возврата -> подтверждение возврата -> отправка назад -> получение продавцом обратно
     * 
     * @test
     */
    public function dealWithShippingBack()
    {
        $app = new App();
        
        //Создание пользователей
        $supplier = $app->getUsers()->reg(array(
            'name' => "supplier",
            'email' => "supplier".rand(0,1000)."@test.ru",
            'accepts_conditions' => true
        ));
        self::$logger->info(print_r($supplier,1));
        
        $consumer = $app->getUsers()->reg(array(
            'name' => "consumer",
            'email' => "consumer".rand(0,1000)."@test.ru",
            'accepts_conditions' => true
        ));
        self::$logger->info(print_r($consumer,1));
        
        $this->assertNotEmpty($supplier['id']);
        $this->assertNotEmpty($consumer['id']);
        
        //Создание сделки
        $order = $app->getOrders($supplier['id'])->create(array(
            'title' => 'Order test #'.rand(1,9999),
            'order_description' => 'order description',
            'cost' => rand(10000, 100000),
            'supplier_id' => $supplier['id'],
            'consumer_id' => $consumer['id'],
            'commission_payer' => Payers::FIFTY_FIFTY
        ));
        self::$logger->info(print_r($order,1));
        
        $this->assertNotEmpty($order['id']);
        
        //Платежная информация
        $supplierBillingInfo = $app->getOrders($supplier['id'])->getBilling($order['id'])->create(array(
            'holder_type' => PayerTypes::PERSONAL,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'name' => 'name lastname',
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        ));
        self::$logger->info(print_r($supplierBillingInfo,1));
        $this->assertNotEmpty($supplierBillingInfo['id']);
        
        $consumerBillingInfo = $app->getOrders($consumer['id'])->getBilling($order['id'])->create(array(
            'holder_type' => PayerTypes::PERSONAL,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'name' => 'name lastname',
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        ));
        self::$logger->info(print_r($consumerBillingInfo,1));
        $this->assertNotEmpty($consumerBillingInfo['id']);
        
        //Подтверждение и оплата
        $bill = $app->getOrders($consumer['id'])->getPayments($order['id'])->createBill("Ivanov Ivan");
        self::$logger->info(print_r($bill,1));
        $this->assertNotEmpty($bill['id']);
        
        $transitionState = $app->getOrders($consumer['id'])->getTransitions($order['id'])->doTransition("payment_verification");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Ждем пока переведется статус
        while(true)
        {
            sleep(60);
            $order = $app->getOrders($consumer['id'])->getByID($order[id]);
            if($order['state'] == 'paid') {
                break;
            }
        }
        
        //Создание доставки
        $shipping = $app->getOrders($supplier['id'])->getShipping($order['id'])->create(array(
            "company" => "Delivery Club"
        ));
        self::$logger->info(print_r($shipping,1));
        $this->assertNotEmpty($shipping['id']);
        
        //Передаем в доставку
        $transitionState = $app->getOrders($supplier['id'])->getTransitions($order['id'])->doTransition("shipping");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Отправляем
        $transitionState = $app->getOrders($supplier['id'])->getTransitions($order['id'])->doTransition("received");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Создание жалобы
        $claim = $app->getOrders($consumer['id'])->getClaims($order['id'])->create(array(
            "reason" => ClaimReasons::PACKAGE_BROKEN,
            "description" => "Lorem ipsum sit dolor"
        ));
        self::$logger->info(print_r($claim,1));
        $this->assertNotEmpty($claim['id']);

        $transitionState = $app->getOrders($consumer['id'])->getTransitions($order['id'])->doTransition("rejected");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Возврат заказа
        $changes = $app->getOrders($consumer['id'])->getChanges($order['id'])->create(array(
            'change_type' => ChangeTypes::PURCHASE_RETURN
        ));
        self::$logger->info(print_r($changes,1));
        $this->assertEquals($changes['id'], true);
        
        $changeState = $app->getOrders($supplier['id'])->getChanges($order['id'])->confirm($changes['id']);
        self::$logger->info(print_r($changeState,1));
        
        //Оформляем возврат
        $shipping = $app->getOrders($supplier['id'])->getShipping($order['id'])->create(array(
            "company" => "Delivery Club"
        ), true);
        self::$logger->info(print_r($shipping,1));
        $this->assertNotEmpty($shipping['id']);
        
        $transitionState = $app->getOrders($consumer['id'])->getTransitions($order['id'])->doTransition("shipping_back");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
        //Товар возвращен
        $transitionState = $app->getOrders($supplier['id'])->getTransitions($order['id'])->doTransition("returned");
        self::$logger->info(print_r($transitionState,1));
        $this->assertEquals($transitionState, true);
        
    }
}