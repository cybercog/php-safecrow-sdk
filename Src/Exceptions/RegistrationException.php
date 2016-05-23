<?
namespace Safecrow\Exceptions;

class RegistrationException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Заполните обязательные поля");
    }
}