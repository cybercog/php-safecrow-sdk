<?php

namespace Safecrow;

use Safecrow\Http\Client;

class Subscriptions
{
    private $client;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * Создание подписки
     * 
     * @param string $url
     * @param array $states
     * @param string $subscribeId
     * @return array
     */
    public function subscribe($url, array $states, $subscribeId = null)
    {
        $data = array(
            'url' => $url,
            'to_states' => $states
        );
        
        if($subscribeId !== null) {
            $data['subscription_id'] = $subscribeId;
        }
        
        $res = $this->getClient()->post("/subscriptions", $data);
        
        return $res['app_subscription'] ?: $res;
    }
    
    /**
     * Получение списка подписок
     * 
     * @return array
     */
    public function getList()
    {
        $res = $this->getClient()->get("/subscriptions");
        
        return $res['app_subscriptions'] ?: $res;
    }
    
    /**
     * Удаление подписки
     * 
     * @param unknown $subscribeId
     * @return unknown
     */
    public function unsubscribe($subscribeId)
    {
        $status = false;
        $res = $this->getClient()->delete("/subscriptions/{$subscribeId}", null, $status);
        
        return $status ?: $res;
    }
    
    /**
     * Подверждение подписки
     * 
     * @param unknown $subscribeId
     * @return unknown
     */
    public function confirm($subscribeId)
    {
        $status = false;
        $res = $this->getClient()->post("/subscription/{$subscribeId}/confirm", null, $status);
        
        return $sttaus ?: $res;
    }
    
    private function getClient()
    {
        return $this->client;
    }
}