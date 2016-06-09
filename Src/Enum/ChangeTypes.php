<?php

namespace Safecrow\Enum;

class ChangeTypes
{
    const PROLONG_PROTECTION = "prolong_protection";
    const CHANGE_CONDITIONS = "change_conditions";
    const PURCHASE_RETURN = "purchase_return";
    
    public static function getChangeTypes()
    {
        $oReflection = new \ReflectionClass(self::class);
        return $oReflection->getConstants();
    }
}