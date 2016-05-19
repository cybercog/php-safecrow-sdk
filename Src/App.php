<?
namespace Safecrow;

class App
{
    private static
        $devHost = "http://dev.safecrow.ru/api/v1",
        $prodHost = "https://www.safecrow.ru/api/v1",
        $arAllowedMimes = [
            //Text
            "text" => ["text/plain", "text/csv", "text/rtf", "application/rtf"],
            //Images
            "image" => ["image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/png", "image/svg+xml", "image/tiff"],
            //Ms Word
            "word" => ["application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
            //Ms Excel
            "excel" => ["application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"],
            //pdf
            "pdf" => ["application/pdf"]
        ]
    ;
    
    private
        $key,
        $secret,
        $host,
        $user
    ;
    
    /**
     * @package Safecrow
     * 
     * @param string $key
     * @param string $secret
     * @param bool $bTest
     */
    public function __construct($key, $secret, $bTest = false)
    {
        $this->key = $key;
        $this->secret = $secret;
        
        $this->host = $bTest ? self::$devHost : self::$prodHost;
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function getSecret()
    {
        return $this->secret;
    }
    
    public function getHost()
    {
        return $this->host;
    }
    
    public function IsAllowedFileType($type)
    {
        foreach (self::$arAllowedMimes as $group) {
            if(in_array($type, $group)) {
                return true;
            }
        }
        
        return false;
    }
}