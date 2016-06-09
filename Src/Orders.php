<?php
namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Enum\Payers;
use Safecrow\Exceptions\OrderCreateException;
use Safecrow\Helpers\FilesHelper;

class Orders
{
    private 
        $client,
        $userId
    ;
    
    public function __construct(Client $client, $userId)
    {
        $this->client = $client;
        $this->userId = $userId;
    }
    
    /**
     * ��������� ������� ��� ������ �� �������
     * 
     * @param int $orderId
     * @return \Safecrow\Billing
     */
    public function getBilling($orderId)
    {
        return new Billing($this->getClient(), $orderId);
    }
    
    /**
     * ��������� ������� ��� ������ � ����������� ������
     * 
     * @param int $orderId
     * @return \Safecrow\Changes
     */
    public function getChanges($orderId)
    {
        return new Changes($this->getClient(), $orderId);
    }
    
    /**
     * ��������� ������� ��� ������ � ��������
     * 
     * @param int $orderId
     * @return \Safecrow\Claims
     */
    public function getClaims($orderId)
    {
        return new Claims($this->getClient(), $orderId);
    }
    
    /**
     * ��������� ������� ��� ������ � �������
     * 
     * @param int $orderId
     * @return \Safecrow\Payments
     */
    public function getPayments($orderId)
    {
        return new Payments($this->getClient(), $orderId);
    }
    
    /**
     * ��������� ������� ��� ������ � ���������/���������
     * 
     * @param int $orderId
     * @return \Safecrow\Shipping
     */
    public function getShipping($orderId)
    {
        return new Shipping($this->getClient(), $orderId);
    }
    
    /**
     * ��������� ������� ��� ������ � ����������
     * 
     * @param int $orderId
     * @return \Safecrow\Transitions
     */
    public function getTransitions($orderId)
    {
        return new Transitions($this->getClient(), $orderId);
    }
    
    /**
     * �������� ������ ������
     * 
     * @param array $order
     * @return array
     */
    public function createOrder(array $order)
    {
        $this->validate($order);
        if(!empty($order['attachments'])) {
            $order['attachments'] = $this->processFiles($order['attachments']);
        }
        
        if(!(int)$order['supplier_id']) {
            $order['supplier_id'] = $this->userId;
        }
        
        if(!(int)$order['verify_days']) {
            $order['verify_days'] = Config::DEFAULT_VERIFY_DAYS;
        }
        
        $res = $this->getClient()->post("/orders", $order);
        
        return $res['order'] ?: $res;
    }
    
    /**
     * ��������������� ������ ��������
     * 
     * @param float $sum
     * @param string $payer
     * 
     * @return array;
     */
    public function calcCommision($sum, $payer)
    {
        if(!(float)$sum || !in_array($payer, Payers::getPayers())) {
            return false;
        }
        
        $res = $this->getClient()->post("/orders/calc_commission", array('cost' => (int)$sum, 'commission_payer' => $payer));
        
        return $res['cost'] ?: $res;
    }
    
    /**
     * �������������� ������
     * 
     * @param int $orderId
     * @param array $fields
     * 
     * @return array|bool
     */
    public function editOrder($orderId, $fields)
    {
        if(!(int)$orderId) {
            return false;
        }
        
        $res = $this->getClient()->patch("/orders/{$orderId}", $fields);
        
        return $res['order'] ?: $res;
    }
    
    /**
     * ��������� ������ �������
     * 
     * @param int $page
     * @param int $per
     * 
     * @return array
     */
    public function getList($page=null, $per=null)
    {
        $params = array();
        if((int)$page) {
            $params['page'] = (int)$page;
        }
        
        if((int)$per) {
            $params['per'] = (int)$per;
        }
        
        return $this->getClient()->get("/orders", $params);
    }
    
    /**
     * ��������� ������ �� ID
     * 
     * @param int $id
     * @return boolean|array
     */
    public function getByID($id)
    {
        if(!(int)$id) {
            return false;
        }
        
        $res = $this->getClient()->get("/orders/{$id}");
        
        return $res['order'] ?: $res;
    }
    
    /**
     * ��������� ����� ������
     * 
     * @param array $order
     * @throws \Safecrow\Exceptions\OrderCreateException
     * @return void
     */
    private function validate(array $order)
    {
        $arErrors = arary();
        
        if(empty($order['order_description'])) {
            $arErrors['order_description'] = '�� ��������� �������� ������';
        }
        
        if(!(float)($order['cost'])) {
            $arErrors['cost'] = '�� ������� ��������� ������';
        }
        
        if(!in_array($order['commission_payer'], Payers::getPayers())) {
            $arErrors['commission_payer'] = '������������ ��� �����������';
        }
        
        if(!empty($arErrors)) {
            $ex = new OrderCreateException("����������� ������������ ����"); 
            $ex->setData($arErrors);
            
            throw $ex;
        }
    }
    
    /**
     * ��������� ���������� �������
     * @param array files
     * @throws \Safecrow\Exceptions\IncorrectAttachmentException
     * @return array
     */
    private function processFiles(array $files)
    {
        //���� �������� ����, �� ���������� �������� ���� � �����
        foreach ($files as $k => $file) {
            if(is_string($file)) {
                $files[$k] = FilesHelper::prepareFile($file);
            }
        }
        
        foreach($files as $k => $file) {
            if(!is_array($file)) {
                unset($files[$k]);
            }
            
            if(!App::IsAllowedFileType($file['content_type'])) {
                throw new IncorrectAttachmentException;
            }
        }
    }

    private function getClient()
    {
        return $this->client;
    }
    
    private function getUsers()
    {
        return $this->$users;
    }
}