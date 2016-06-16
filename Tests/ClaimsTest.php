<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Safecrow;
use Safecrow\App;
use Safecrow\Enum\ClaimReasons;

class ClaimsTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    private
        $claims
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
    
        $this->claims = $orders->getClaims($ordersList[0]['id']);
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/claims.test.log', Logger::INFO));
    }
    
    /**
     * Неудачная попытка создать жалобу с некорректной темой
     * 
     * @test
     * @covers Claims::create
     * @expectedException Safecrow\Exceptions\ClaimsException
     */
    public function failCreateClaim()
    {
        $data = array(
            'reason' => 'reason',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
        );
        
        $this->claims->create($data);
    }
    
    /**
     * Создание жалобы
     * 
     * @test
     * @covers Claims::create
     */
    public function createClaim()
    {
        $data = array(
            'reason' => ClaimReasons::MISSING_GOODS,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
        );
        
        $res = $this->claims->create($data);
        self::$logger->info(print_r($res,1));
        $this->assertEquals($res['reason'], $data['reason']);
        $this->assertEquals($res['description'], $data['description']);
        
        return $res;
    }
    
    /**
     * Получение жалобы
     * 
     * @test
     * @covers Claims::getClaim
     * @depends createClaim
     */
    public function getClaim($claim)
    {
        $res = $this->claims->getClaim();
        
        $this->assertEquals($claim['id'], $res['id']);
    }
}