<? 
namespace Safecrow\Http;

use Safecrow\App;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Query
{
    private 
        $app = NULL,
        $sUrl = NULL,
        $sMethod = NULL,
        $sStatus = 0,
        
        $sUserAgent = 'MGN API Query',
        $sPostData = [],
        $arHeaders = [],
        $arAddHeaders = [],
        $arAddParams = [],
        $arHTTPAuthData = [],
        
        $arInfo = []
    ;
    
    /**
     * Инициализация запроса
     * @param string $method
     * @param string $url
     */
    public function __construct($method, $url)
    {
        $this->sUrl =  $url;
        $this->sMethod = strtoupper($method);
    }
    
    /**
     * Выполняет запрос
     * @return boolean|mixed
     */
    public function exec()
    {
        try 
        {
            if($this->sMethod == 'GET') {
                $this->sUrl .= '?'.http_build_query($this->sPostData); 
            } else { 
                $this->sPostData = http_build_query($this->sPostData);
            }
            
            $ch = curl_init($this->sUrl);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($this->arHeaders, $this->arAddHeaders));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if($this->sMethod != 'POST') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->sMethod);
            } else {
                curl_setopt($ch, CURLOPT_POST, 1);
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->sPostData);
            
            if(!empty($this->arHTTPAuthData)) {
                curl_setopt($ch, CURLOPT_USERPWD, implode(":", $this->arHTTPAuthData));
            }
            
            if(!empty($this->arAddParams)) {
                foreach ($this->arAddParams as $sOpt => $sVal) {
                    curl_setopt($ch, $sOpt, $sVal);
                }
            }
            
            $res = curl_exec($ch);
            
            //Получим всю инфу по запросу
            $this->arInfo = curl_getinfo($ch);
            $this->sStatus = $this->arInfo['http_code'];

            curl_close($ch);
            if(strpos($this->arInfo['content_type'], "application/json") !== false) {
                return json_decode($res, 1);
            } else {
                return $res;
            }
            
        }
        catch (\Exception  $e) 
        {
            return false;
        }
        finally 
        {
            $logger = new Logger('tests');
            $logger->pushHandler(new StreamHandler('Logs/rest.log', Logger::INFO));
            
            $logger->info(json_encode([
                'method' => $this->sMethod,
                'request' => $this->sUrl,
                'data' => $this->sPostData,
                'response' => $this->arInfo,
                'responseData' => $res
            ]));
        }
    }
    
    /**
     * Устанавливает данные запроса
     * @param array $arPostData
     */
    public function setPostData(array $arPostData)
    {
        $this->sPostData = $arPostData;
    }
    
    /**
     * Устанавливае данные HTTP basic Auth
     * @param $sLogin
     * @param sPass 
     */
    public function setHttpAuthData($sLogin, $sPass)
    {
        $this->arHTTPAuthData = array('LOGIN' => $sLogin, 'PASS' => $sPass);
    }
    
    public function setAddParams($arParams)
    {
        if(is_array($arParams))
            $this->arAddParams = $arParams;
    }
    
    public function getStatus()
    {
        return $this->sStatus;
    }
    
    public function getResponse()
    {
        return $this->arInfo;
    }
    
    public function isSuccess()
    {
        return ($this->getStatus() >= 200 && $this->getStatus() < 300);
    }
}