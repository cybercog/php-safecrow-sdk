<?
namespace Safecrow\Tests;

use Safecrow\Exceptions\OrderCreateException;
use Safecrow\Enum\Payers;

class OrdersTest extends \PHPUnit_Framework_TestCase
{
    private static
        $logger
    ;
    
    private
        $app,
        $users,
        $orders,
        
        $order,
        $currentUser,
        $otherUser
    ;
    
    /**
     * @before
     */
    public function createApp()
    {
        $this->app = new App(Config::API_KEY, Config::API_SECRET, true);
        
        $this->users = new Users($this->app);
        $this->currentUser = $this->users->getByEmail("test2220@test.ru");
        $this->otherUser = $this->users->getByPhone("89999216803");
        
        $this->orders = new Orders($this->app, $this->currentUser);
    
        self::$logger = new Logger('tests');
        self::$logger->pushHandler(new StreamHandler('Logs/user.test.log', Logger::INFO));
    }
    
    /**
     * test
     */
    public function orderCreateUnsuccess()
    {
        $this->expectException(OrderCreateException::class);
        $this->orders->create([
            'title' => 'Order test #'.rand(1,9999)
        ]);
    }
    
    /**
     * test
     */
    public function orderCreateWithoutUser()
    {
        $data = [
            'title' => 'Order test #'.rand(1,9999),
            'order_description' => 'order description',
            'cost' => rand(10000, 100000),
            'commission_payer' => Payers::CONSUMER
        ];
        
        $order = $this->orders->create($data);
        
        $this->assertGreaterThan(0, $order['id']);
        $this->assertEquals($order['title'], $data['title']);
        $this->assertEquals($order['order_description'], $data['order_description']);
        $this->assertEquals($order['cost'], $data['cost']);
        $this->assertEquals($order['commission_payer'], $data['commission_payer']);
        $this->assertEquals($order['state'], 'pending');
        $this->assertEquals($order['owner_id'], $this->currentUser['id']);
        $this->assertEquals($order['supplier_id'], $this->currentUser['id']);
        $this->assertEquals($order['role'], Payers::SUPPLIER);
    }
    
    
}