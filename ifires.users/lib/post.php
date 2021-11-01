<?
namespace Ifires\Users;

use Bitrix\Main\Entity;

class PostTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'ifires_post';
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
            ])
        );
    }
}