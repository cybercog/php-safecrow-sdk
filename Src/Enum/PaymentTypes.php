<?php

namespace Safecrow\Enum;

class PaymentTypes
{
    const BANK_ACCOUNT = "bank_account";
    const CARD = "card";
    
    public static function getPaymentTypes()
    {
        $oReflection = new \ReflectionClass(__CLASS__);
        return $oReflection->getConstants();
    }
}