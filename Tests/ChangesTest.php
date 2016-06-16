<?php

namespace Safecrow\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Safecrow\App;
use Safecrow\Enum\ChangeTypes;
use Safecrow\Enum\ChangeStates;
use Safecrow;

class ChangesTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    private
        $changes
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
    
        $this->changes = $orders->getChanges($ordersList[0]['id']);
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/changes.test.log', Logger::INFO));
    }
    
    /**
     * Неудачная попытка создать запрос на продление защиты сделки 
     * 
     * @test
     * @covers Changes::create
     * @expectedException Safecrow\Exceptions\ChangesException
     */
    public function failedCreatePrologProtection()
    {
        $data = array(
            'change_type' => ChangeTypes::PROLONG_PROTECTION
        );
        
        $this->changes->create($data);
    }
    
    /**
     * Неудачная попытка создать запрос на изменение условий сделки
     * 
     * @test
     * @covers Changes::create
     * @expectedException Safecrow\Exceptions\ChangesException
     */
    public function failedCreateChangeConditions()
    {
        $data = array(
            'change_type' => ChangeTypes::CHANGE_CONDITIONS
        );
        
        $this->changes->create($data);
    }
    
    /**
     * Создание запроса на продление защиты сделки
     * 
     * @test
     * @covers Changes::create
     */
    public function createChanges()
    {
        $data = array(
            'change_type' => ChangeTypes::PROLONG_PROTECTION,
            'prolong_protection_to' => date('c', time()+1*86400)
        );
        
        $res = $this->changes->create($data);
        
        $this->assertNotEmpty($res['id']);
        $this->assertEquals($res['state'], ChangeStates::PENDING);
        self::$logger->info(json_encode($res));
        return $res;
    }
    
    /**
     * Отклонение запроса на изменение
     * 
     * @test
     * @covers Changes::reject
     * @depends createChanges
     */
    public function rejectChanges($change)
    {
        $res = $this->changes->reject($change['id']);
        self::$logger->info(json_encode($res));
        $this->assertEquals($res['state'], ChangeStates::REJECTED);
    }
    
    /**
     * Примем запрос на изменение
     *
     * @test
     * @covers Changes::create
     * @depends createChanges
     */
    public function changeConditions($change)
    {
        $res = $this->changes->confirm($change['id']);
        self::$logger->info(json_encode($res));
        $this->assertEquals($res['state'], ChangeStates::CONFIRM);
    }
}