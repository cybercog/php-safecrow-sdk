<?
namespace Safecrow;

class Config
{
    const ENVIROMENT = "dev";
    
    const API_KEY = "b9598ffa-f905-4908-92b1-90e602baa2b2";
    const API_SECRET  = "a6420c86bdd9fe871315210e13eed817fc88de887b9ebe953edfae46174c9434";
    
    //Время жизни пользовательского токена
    const USER_TOKEN_LIFETIME = 60*15; //15 min
    
    //Время валидации заказа
    const DEFAULT_VERIFY_DAYS = 21;
    
    const DEV_HOST = "http://dev.safecrow.ru/api/v1";
    const PROD_HOST = "https://www.safecrow.ru/api/v1";
    
    const ALLOWED_FILE_TYPES = array(
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
}