<?php

namespace Safecrow;

use Safecrow\Http\Client;

class Transitions
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
     * Получение списка доступных переходов
     * 
     * @return array
     */
    public function getList()
    {
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/transitions");
        
        return $res['transitions'] ?: $res;
    }
    
    /**
     * Совершить переход
     * 
     * @param string $state
     * @return array | string
     */
    public function doTransition($state)
    {
        $res = $this->getClient()->post("/orders/{$this->getOrderId()}/transitions", array('transition' => array('to_state' => $state)));
        
        return empty($res) ?: $res;
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