<?php

namespace Safecrow\Enum;

class ChangeStates
{
    const PENDING = "pending";
    const CONFIRM = "confirm";
    const REJECTED = "rejected";
    
    public function getChangeStates()
    {
        $oReflection = new \ReflectionClass(self::class);
        return $oReflection->getConstants();
    }
}