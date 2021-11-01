<?
namespace Ifires\Users;

use Bitrix\Main\Entity;

class OfficeTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'ifires_office';
    }
    
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Entity\StringField('NAME', [
                'required' => true
            ]),
            new Entity\StringField('ADDRESS', [
                'required' => true
            ])
        );
    }
}