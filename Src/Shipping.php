<?php

namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Helpers\FilesHelper;

class Shipping
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
     * Создание запроса на возврат/доставку
     * 
     * @param array $fields
     * @return array
     */
    public function create($fields)
    {
        if(!empty($fields['attachment'])) {
            $fields['attachment'] = $this->processFiles($fields['attachment']);
        }
        
        $res = $this->getClient()->post("/orders/{$this->getOrderId()}/shipping", $fields);
        
        return $res['tracking'] ?: $res;
    }
    
    /**
     * Получение информации о доставке
     * 
     * @return array
     */
    public function getShipping()
    {
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/shipping");
        
        return $res['tracking'] ?: $res;
    }
    
    /**
     * Получение информации о возврате
     * 
     * @return array
     */
    public function getShippingBack()
    {
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/shipping_back");
        
        return $res['tracking'] ?: $res;
    }
    
    private function getClient()
    {
        return $this->client;
    }
    
    private function getOrderId()
    {
        return $this->orderId;
    }
    
    /**
     * Проверяет содержимое массива
     * @param array files
     * @throws \Safecrow\Exceptions\IncorrectAttachmentException
     * @return array
     */
    private function processFiles(array $files)
    {
        //Если передали урлы, то попытаемся получить инфу о файле
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
}