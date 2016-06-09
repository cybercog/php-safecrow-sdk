<?php

namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Enum\PayerTypes;
use Safecrow\Enum\PaymentTypes;
use Safecrow\Exceptions\BillingException;

class Billing
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
     * Проставление платежной информации текущего пользователя
     * Внимание! Параметры не нужно заворачивать в переменнеую billing_info
     * 
     * @param array $fields
     * @return array
     */
    public function createBillingInfo($fields)
    {
        $this->validateBillingInfo($fields);
        
        $res = $this->getClient()->post("/orders/{$this->getOrderId()}/billing_info", array("billing_info" => $fields));

        return $res['billing_info'] ?: $res;
    }
    
    /**
     * Получение платежной информации текущего пользователя в текущей сделке
     * 
     * @return array
     */
    public function getBillingInfo()
    {
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/billing_info");
        
        return $res['billing_info'] ?: $res;
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
     * Валидация полей платежной информации
     * 
     * @param array $fields
     * @throws \Safecrow\Exceptions\BillingException
     */
    private function validateBillingInfo($fields)
    {
        $arErrors = array();
        if(!in_array($fields['holder_type'], PayerTypes::getPayerTypes())) {
            $arErrors['holder_type'] = 'Некорректный тип плательщика';
        }
        
        if(!int_array($fields['billing_type'], PaymentTypes::getPaymentTypes())) {
            $arErrors['billing_type'] = 'Недопустимый тип оплаты';
        }
        
        if(
            $fields['holder_type'] == PayerTypes::PERSONAL && 
            $fields['billing_type'] == PaymentTypes::BANK_ACCOUNT &&
            empty($fields['payment_params']['name'])
        ) {
            $arErrors['payment_params']['name'] = "Не указано имя плательщика";
        }
        
        if(empty($fields['payment_params']['bik'])) {
            $arErrors['payment_params']['bik'] = "Не указан БИК";
        }
        
        if(empty($fields['payment_params']['account'])) {
            $arErrors['payment_params']['account'] = "Не указан расчетный счет";
        }
        
        if($fields['holder_type'] == PayerTypes::BUSINESS && $fields['billing_type'] == PaymentTypes::BANK_ACCOUNT) {
            if(empty($fields['payment_params']['ogrn'])) {
                $arErrors['payment_params']['ogrn'] = 'Не указан ОГРН';
            }
            
            if(empty($fields['payment_params']['inn'])) {
                $arErrors['payment_params']['inn'] = 'Не указан ИНН';
            }
        }
        
        if(!empty($arErrors)) {
            $ex = new BillingException('Не заполнены обязательные поля');
            $ex->setData($arErrors);
            
            throw $ex;
        }
    }
}