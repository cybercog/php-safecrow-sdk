<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Safecrow\Enum\PayerTypes;
use Safecrow\Enum\PaymentTypes;
use Safecrow\App;

/**
 * @backupGlobals
 */
class BillingTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;

    private
        $billing
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
        
        $this->billing = $orders->getBilling($ordersList[0]['id']);

        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/billing.test.log', Logger::INFO));
    }
    
    /**
     * Неудачная попытка создания счета только с holder_type
     * 
     * @test
     * @covers Billing::create
     * @expectedException Safecrow\Exceptions\BillingException
     */
    public function createWithHolderType()
    {
        $data = array(
            'holder_type' => PayerTypes::BUSINESS
        );
        
        $this->billing->create($data);
    }
    
    /**
     * Неудачная попытка создать счет для физ. лица без обязательных полей
     * 
     * @test
     * @covers Billing::create
     * @expectedException Safecrow\Exceptions\BillingException
     */
    public function createPersonalBilling()
    {
        $data = array(
            'holder_type' => PayerTypes::PERSONAL,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        );
        
        $this->billing->create($data);
    }
    
    /**
     * Неудачная попытка создать счет для юр. лица без обязательных полей
     * 
     * @test
     * @cover Billing::create
     * @expectedException Safecrow\Exceptions\BillingException
     */
    public function createBusinessBilling()
    {
        $data = array(
            'holder_type' => PayerTypes::BUSINESS,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        );
        
        $this->billing->create($data);
    }
    
    /**
     * Удачная попытка создать счет для физ лица - банковский счет
     * 
     * @test
     * @cover Billing::create
     */
    public function createPublicBillingWithAccount()
    {
        $data = array(
            'holder_type' => PayerTypes::PERSONAL,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'name' => 'name lastname',
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        );
        
        $res = $this->billing->create($data);

        $this->assertNotEmpty($res['id']);
        $this->assertEquals($res['holder_type'], $data['holder_type']);
        $this->assertEquals($res['billing_type'], $data['billing_type']);
    }
    
    /**
     * Удачная попытка создать счет для физ лица - карта
     * 
     * @test
     * @cover Billing::create
     */
    public function createPublicBillingWithCard()
    {
        $data = array(
            'holder_type' => PayerTypes::PERSONAL,
            'billing_type' => PaymentTypes::CARD,
            'payment_params' => array(
                'name' => 'name lastname',
                'card' => '4276840102456868',
                'bik' => '040147000',
                'account' => '12345678900987654321'
            )
        );
        
        $res = $this->billing->create($data);

        $this->assertNotEmpty($res['id']);
        $this->assertEquals($res['holder_type'], $data['holder_type']);
        $this->assertEquals($res['billing_type'], $data['billing_type']);
    }
    
    /**
     * Удачная попытка создать счет для юр. лица 
     * 
     * @test
     * @cover Billing::create
     */
    public function createBusinessBillingWithAccount()
    {
        $data = array(
            'holder_type' => PayerTypes::BUSINESS,
            'billing_type' => PaymentTypes::BANK_ACCOUNT,
            'payment_params' => array(
                'organization' => 'org name',
                'bik' => '040147000',
                'account' => '12345678900987654321',
                'ogrn' => '1234567890123',
                'inn' => '1234567890'
            )
        );
        
        $res = $this->billing->create($data);

        $this->assertNotEmpty($res['id']);
        $this->assertEquals($res['holder_type'], $data['holder_type']);
        $this->assertEquals($res['billing_type'], $data['billing_type']);
    }
}