<?php
namespace Safecrow;

use Safecrow\Interfaces\IConfig;

class Config implements IConfig
{
    //Время жизни пользовательского токена
    const USER_TOKEN_LIFETIME = 900; //15 min
    
    //Время валидации заказа
    const DEFAULT_VERIFY_DAYS = 21;
    
    const DEV_HOST = "http://dev.safecrow.ru/api/v1";
    const PROD_HOST = "https://www.safecrow.ru/api/v1";
    
    private static
        $apiKey = "b9598ffa-f905-4908-92b1-90e602baa2b2",
        $apiSecret = "a6420c86bdd9fe871315210e13eed817fc88de887b9ebe953edfae46174c9434"
    ;
    
    protected 
        $enviroment;
    
    public static $arAllowedFileTypes = array(
        //Text
        "text" => array("text/plain", "text/csv", "text/rtf", "application/rtf"),
        //Images
        "image" => array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/png", "image/svg+xml", "image/tiff"),
        //Ms Word
        "word" => array("application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"),
        //Ms Excel
        "excel" => array("application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"),
        //pdf
        "pdf" => array("application/pdf")
    );
    
    public function __construct($enviroment)
    {
        $this->enviroment = $enviroment;
    }
    
    public function getSecret()
    {
        return self::$apiSecret;
    }
    
    public function getToken()
    {
        return self::$apiKey;
    }
    
    public function getHost()
    {
        return $this->enviroment == "dev" ? self::DEV_HOST : self::PROD_HOST;
    }
}