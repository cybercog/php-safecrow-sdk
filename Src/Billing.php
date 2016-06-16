<?php

namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Enum\PayerTypes;
use Safecrow\Enum\PaymentTypes;
use Safecrow\Exceptions\BillingException;

class Billing extends \PHPUnit_Framework_TestCase
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
    public function create($fields)
    {
        $this->validateBillingInfo($fields);
        
        $res = $this->getClient()->post("/orders/{$this->getOrderId()}/billing_info", array("billing_info" => $fields));

        return isset($res['billing_info']) ? $res['billing_info'] : $res;
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
        if(!isset($fields['holder_type']) || !in_array($fields['holder_type'], PayerTypes::getPayerTypes())) {
            $arErrors['holder_type'] = 'Некорректный тип плательщика';
        }
        
        if(!isset($fields['billing_type']) ||  !in_array($fields['billing_type'], PaymentTypes::getPaymentTypes())) {
            $arErrors['billing_type'] = 'Недопустимый тип оплаты';
        }
        
        if(empty($fields['payment_params']['bik'])) {
            $arErrors['payment_params']['bik'] = "Не указан БИК";
        }
        
        if(empty($fields['payment_params']['account'])) {
            $arErrors['payment_params']['account'] = "Не указан расчетный счет";
        }
        
        
        
        if(isset($fields['holder_type']) && isset($fields['billing_type'])) {
            if(empty($fields['payment_params']['name']) && $fields['holder_type'] == PayerTypes::PERSONAL) {
                $arErrors['payment_params']['name'] = "Не указано имя плательщика";
            }    
            
            if($fields['holder_type'] == PayerTypes::BUSINESS && $fields['billing_type'] == PaymentTypes::BANK_ACCOUNT) {
                if(empty($fields['payment_params']['organization'])) {
                    $arErrors['payment_params']['organization'] = 'Не указано название огранизации';
                }
                
                if(empty($fields['payment_params']['ogrn'])) {
                    $arErrors['payment_params']['ogrn'] = 'Не указан ОГРН';
                }
        
                if(empty($fields['payment_params']['inn'])) {
                    $arErrors['payment_params']['inn'] = 'Не указан ИНН';
                }
            }
        }
        
        if(!empty($arErrors)) {
            $ex = new BillingException('Не заполнены обязательные поля');
            $ex->setData($arErrors);
            
            throw $ex;
        }
    }
}