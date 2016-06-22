<?php
namespace Safecrow\Enum;

class Payers
{
    const SUPPLIER = "supplier";
    const CONSUMER = "consumer";
    const FIFTY_FIFTY = "50/50";
    
    public static function getPayers()
    {
        $oReflection = new \ReflectionClass(__CLASS__);
        return $oReflection->getConstants();
    }
}