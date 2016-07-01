#Safecrow SDK#
  
Реализация основных методов API для интеграции с сервисом [Safecrow](https://www.safecrow.ru/)
  
##Установка##
  
```
composer require mgn/safecrow-sdk
```
  
  
##Релизы##
- 1.0.1 - Передача настроек приложения через объект реализующий интерфейс IConfig
- 1.0.0 - Реализация основных методов

##Использования##
   
###Конфигурация приложения###
```php
use Safecrow\Config;

$config = new Config();
```

Класс Config реализует интерфейс IConfig.
  
  
###Создание приложения###
```php
$app = new App($config);
```
  
  
###Использование###
  
Подписка на обновления
```php
$app->getSubscriptions()->subscribe("http://safecrow.mgnexus.ru/subscription", array("paid"));
```

Регистрация пользователя
```php
$user = $app->getUsers()->reg(array(
    'name' => $userName,
    'email' => $userEmail,
    'accepts_conditions' => true
));
```

Создание заказа
```php
$order = $app->getOrders($userId)->create(array(
    'title' => 'Order test #9999',
    'order_description' => 'order description',
    'cost' => 100000,
    'commission_payer' => Payers::CONSUMER
));
```