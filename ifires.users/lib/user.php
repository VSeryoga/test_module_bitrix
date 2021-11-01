<?
namespace Ifires\Users;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class UserTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'ifires_user';
    }
    
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Entity\StringField('FIO', [
                'required' => true
            ]),
            new Entity\IntegerField('PHONE', [
                'required' => true,
                'validation' => function() {
                    return [
                        function($value){
                            if (strlen($value) &&  preg_match('/^\d{4}$/', $value))
                            {
                                return true;
                            }else{
                                return 'Телефон должен быть в формате 9999';
                            }
                        }
                    ];
                }
            ]),
            new Entity\IntegerField('POST_ID', [
                'required' => true
            ]),
            new Entity\IntegerField('OFFICE_ID', [
                'required' => true
            ]),
            (new Reference(
                'POST',
                PostTable::class,
                Join::on('this.POST_ID', 'ref.ID')
            ))
            ->configureJoinType('inner'),
            (new Reference(
                'OFFICE',
                OfficeTable::class,
                Join::on('this.OFFICE_ID', 'ref.ID')
            ))
            ->configureJoinType('inner')
        );
    }
}