<?php

namespace Safecrow\Exceptions;

class SafecrowException extends \Exception
{
    protected $arData = array();
    
    public function __construct($message = NULL, $code = NULL, Exception $preview = NULL)
    {
        return parent::__construct($message, $code, $preview);
    }
    
    public function getData()
    {
        return $this->arData;
    }
    
    public function setData(array $arData)
    {
        $this->arData = $arData;
    }
}
