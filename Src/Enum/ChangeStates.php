<?php

namespace Safecrow\Enum;

class ChangeStates
{
    const PENDING = "pending";
    const CONFIRM = "confirm";
    const REJECTED = "rejected";
    
    public function getChangeStates()
    {
        $oReflection = new \ReflectionClass(__CLASS__);
        return $oReflection->getConstants();
    }
}