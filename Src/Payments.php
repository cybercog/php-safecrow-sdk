<?php

namespace Safecrow;

use Safecrow\Http\Client;

class Payments
{
    private 
        $client,
        $orderId
    ;
    
    public function __construct(Client $client, $orderId)
    {
        $this->client = $client;
        $this->orderId = $orderId;
    }
    
    /**
     * Получение информации о стоимости и  оплате
     * 
     * @param[optional] string $redirect
     * @param[optional] string $description
     * @return array
     */
    public function getInfo($redirect = null, $description = null)
    {
        $data = array();
        if($redirect !== null) {
            $data['redirect_to'] = $redirect;
        }
        
        if($description !== null) {
            $data['description'] = $description;
        }
        
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/payment_info", $data);
        
        return $res;
    }
    
    /**
     * Создание счета на оплату
     * 
     * @param string $name
     * @param array $info
     * @return array
     */
    public function createBill($name, array $info=null)
    {
        $payment_method = "invoice";
        
        $data = array(
            'name' => $name,
            'payment_method' => $payment_method,
            'info' => $info
        );
        
        $res = $this->getClient()->post("/orders/{$this->getOrderId()}/payment", array("payment" => $data));
        
        return isset($res['payment']) ? $res['payment'] : $res;
    }
    
    /**
     * Просмотр счета на оплату
     * 
     * @return array
     */
    public function getBill()
    {
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/payment");
        
        return isset($res['payment']) ? $res['payment'] : $res;
    }
    
    
    /**
     * Получение ссылки на скачивание квитанции
     * @return string
     */
    public function downloadInvoice()
    {
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/download_invoice");
        
        return $res;
    }
    
    private function getClient()
    {
        return $this->client;
    }
    
    private function getOrderId()
    {
        return $this->orderId;
    }
}