<?
namespace Safecrow\Tests;

use Safecrow\Exceptions\OrderCreateException;
use Safecrow\Enum\Payers;
use Safecrow\Billing;
use Safecrow\Changes;
use Safecrow\Claims;
use Safecrow\Payments;
use Safecrow\Shipping;
use Safecrow\Transitions;
use Safecrow\App;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * @backupGlobals
 */
class OrdersTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    private
        $orders,
        $curUser,
        $otherUser
    ;
    
    /**
     * @before
     */
    public function createApp()
    {
        $app = new App();
        
        $this->curUser = $app->getUsers()->getByEmail("test596@test.ru");
        $this->otherUser = $app->getUsers()->getByEmail("test8443@test.ru");
        
        $this->orders = $app->getOrders($this->curUser['id']);
        
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/orders.test.log', Logger::INFO));
    }
    
    /**
     * Неудачная попытка создать заказ
     * 
     * @test
     * @covers Orders::create
     * @expectedException Safecrow\Exceptions\OrderCreateException
     */
    public function orderCreateUnsuccess()
    {
        $this->orders->create(array(
            'title' => 'Order test #'.rand(1,9999)
        ));
    }
    
    /**
     * Создание заказа без указания пользователя
     * 
     * @test
     * @covers Orders::create
     */
    public function orderCreateWithoutUser()
    {
        $data = array(
            'title' => 'Order test #'.rand(1,9999),
            'order_description' => 'order description',
            'cost' => rand(10000, 100000),
            'commission_payer' => Payers::CONSUMER
        );
        
        $order = $this->orders->create($data);

        $this->assertEquals($order['title'], $data['title']);
        $this->assertEquals($order['order_description'], $data['order_description']);
        $this->assertEquals($order['cost'], $data['cost']);
        $this->assertEquals($order['commission_payer'], $data['commission_payer']);
        $this->assertEquals($order['state'], 'pending');
        $this->assertEquals($order['owner_id'], $this->curUser['id']);
        $this->assertEquals($order['supplier_id'], $this->curUser['id']);
        $this->assertEquals($order['role'], Payers::SUPPLIER);
        
        return $order;
    }
    
    /**
     * Создание заказа с указанием текущего пользователя как покупателя
     * 
     * @test
     * @covers Orders::create
     */
    public function orderCreateAsConsumer()
    {
        $data = array(
            'title' => 'Order test #'.rand(1,9999),
            'order_description' => 'order description',
            'cost' => rand(10000, 100000),
            'supplier_id' => $this->otherUser['id'],
            'consumer_id' => $this->curUser['id'],
            'commission_payer' => Payers::FIFTY_FIFTY
        );
        
        $order = $this->orders->create($data);
        
        $this->assertEquals($order['owner_id'], $this->curUser['id']);
        $this->assertEquals($order['supplier_id'], $this->otherUser['id']);
        $this->assertEquals($order['role'], Payers::CONSUMER);
        
        return $order;
    }
    
    /**
     * Расчет комиссии
     * 
     * @test
     * @covers Orders::calcComission
     */
    public function calcComission()
    {
        $calc = $this->orders->calcComission(1000000, Payers::FIFTY_FIFTY);
        $this->assertArrayHasKey('cost', $calc);
    }
    
    /**
     * Редактирование заказа
     * 
     * @test
     * @covers Orders::editOrder
     * @depends orderCreateAsConsumer
     */
    public function editOrder($order)
    {
        $data = array(
            'commission_payer' => Payers::SUPPLIER
        );
        
        $updated = $this->orders->editOrder($order['id'], $data);
        
        $this->assertEquals($order['id'], $updated['id']);
        $this->assertEquals($updated['commission_payer'], $data['commission_payer']);
    }
    
    /**
     * Получение списка заказов
     * 
     * @test
     * @covers Orders::getList
     * @depends orderCreateWithoutUser
     */
    public function getList($order)
    {
        $bFind = false;
        $orders = $this->orders->getList();
        
        foreach ($orders as $arOrder) {
            if($arOrder['id'] == $order['id']) {
                $bFind = true;
                break;
            }
        }
        
        $this->assertEquals($bFind, true);
    }
    
    /**
     * Получение заказа по Id
     * 
     * @test
     * @covers Orders::getById
     * @depends orderCreateWithoutUser
     */
    public function getById($order)
    {
        $finded = $this->orders->getById($order['id']);
        $this->assertEquals($order['id'], $finded['id']);
    }
    
    /**
     * Попытка поиска без Id заказа
     * @test
     * @covers Orders::getById
     */
    public function getByIdWithoutId()
    {
        $finded = $this->orders->getById(null);
        $this->assertFalse($finded);
    }
    
    /**
     * Попытка поиска с несуществующим Id
     * 
     * @test
     * @covers Orders::getById
     */
    public function getByIdWithUnexistedId()
    {
        $finded = $this->orders->getById(-1);
        $this->assertArrayHasKey('errors', $finded);
    }
    
    
    /**
     * Получение доступа к основным экземплярам вспомогательных классов
     *
     * @test
     * @covers Orders::getBillings
     * @covers Orders::getChanges
     * @covers Orders::getClaims
     * @covers Orders::getPayments
     * @covers Orders::getShipping
     * @covers Orders::getTransitions
     *
     * @depends orderCreateWithoutUser
     */
    public function getInstancesInstance($order)
    {
        $this->assertInstanceOf(Billing::class, $this->orders->getBilling($order['id']));
        $this->assertInstanceOf(Changes::class, $this->orders->getChanges($order['id']));
        $this->assertInstanceOf(Claims::class, $this->orders->getClaims($order['id']));
        $this->assertInstanceOf(Payments::class, $this->orders->getPayments($order['id']));
        $this->assertInstanceOf(Shipping::class, $this->orders->getShipping($order['id']));
        $this->assertInstanceOf(Transitions::class, $this->orders->getTransitions($order['id']));
    }
}