<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if (class_exists('ifires_users')) {
    return;
}

class ifires_users extends CModule
{
    private $users = [
        'admin' => 'test123',
        'manager' => 'qweasd'
    ];

    public function __construct()
    {
        $this->MODULE_ID = 'ifires.users';
        $this->MODULE_VERSION = '0.0.1';
        $this->MODULE_VERSION_DATE = '2021-10-30 12:00:00';
        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = "Ifires";
        $this->PARTNER_URI = "https://ifires.ru";
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
        $this->InstallFiles();
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        $this->UnInstallFiles();
        ModuleManager::unregisterModule($this->MODULE_ID);
    }
    public function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/components", $_SERVER["DOCUMENT_ROOT"]."/local/components", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/public/api.php", $_SERVER["DOCUMENT_ROOT"]."/api.php", true, true);

        return true;
    }

    function UnInstallFiles()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/local/components/ifires/users");
        \Bitrix\Main\IO\File::deleteFile($_SERVER["DOCUMENT_ROOT"]."/api.php");
        return true;
    }

    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            // Создание таблиц
            \Ifires\Users\UserTable::getEntity()->createDbTable();
            \Ifires\Users\PostTable::getEntity()->createDbTable();
            \Ifires\Users\OfficeTable::getEntity()->createDbTable();

            // Заполнение таблиц 
            $this->installDemoPost();
            $this->installDemoOffice();
            $this->installDemoUser();

            //Добавление пользователей дл яавторизации
            $this->addUsers();
        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            $connection = Application::getInstance()->getConnection();
            $connection->dropTable(\Ifires\Users\UserTable::getTableName());
            $connection->dropTable(\Ifires\Users\PostTable::getTableName());
            $connection->dropTable(\Ifires\Users\OfficeTable::getTableName());
        }

        $this->removeUsers();
    }

    private function installDemoPost()
    {
        $data = [
            'Оператор',
            'Администратор',
            'Управляющий',
            'Специалист',
            'Кассир',
            'Бухгалтер'
        ];

        foreach ($data as $d) {
            \Ifires\Users\PostTable::add([
                'NAME' => $d
            ]);
        }
    }

    private function installDemoOffice()
    {
        $data = [
            ['Северный', 'Дзержинского, 20'],
            ['Центральный', 'Ленина, 51'],
            ['Западный', 'Солнечная, 37А'],
            ['Октябрьский', 'Октябрьский, 98']
        ];

        foreach ($data as $d) {
            \Ifires\Users\OfficeTable::add([
                'NAME' => $d[0],
                'ADDRESS' => $d[1]
            ]);
        }
    }

    private function installDemoUser()
    {
        $data = [
            ['Иванов Дмитрий Сергеевич', '2137', 'Оператор', 'Северный'],
            ['Кочкина Дарья Алексеевна', '2101', 'Администратор', 'Центральный'],
            ['Сергеев Юрий Витальевич', '2112', 'Управляющий', 'Центральный'],
            ['Баранов Сергей Вадимович', '2117', 'Специалист', 'Северный'],
            ['Михайлов Максим Юрьевич', '2122', 'Оператор', 'Западный'],
            ['Фомина Анна Валентиновна', '2120', 'Кассир', 'Центральный'],
            ['Носков Андрей Витальевич', '2131', 'Специалист', 'Октябрьский'],
            ['Соколова Анна Валерьевна', '2130', 'Бухгалтер', 'Центральный'],
            ['Шахматов Алексей Алексеевич', '2125', 'Специалист', 'Западный'],
            ['Ершов Дмитрий Иванович', '2128', 'Специалист', 'Октябрьский']
        ];
        //получим все оффисы
        $res = \Ifires\Users\OfficeTable::getList();
        while ($office = $res->fetch()) {
            $offices[$office['NAME']] = $office['ID'];
        }
        //получим все должности
        $res = \Ifires\Users\PostTable::getList();
        while ($post = $res->fetch()) {
            $posts[$post['NAME']] = $post['ID'];
        }

        foreach ($data as $d) {
            $res = \Ifires\Users\UserTable::add([
                'FIO' => $d[0],
                'PHONE' => $d[1],
                'POST_ID' => $posts[$d[2]],
                'OFFICE_ID' => $offices[$d[3]]
            ]);
        }
    }

    private function addUsers()
    {
        

        foreach ($this->users as $login => $pass) {
            $arFields = [
                'LOGIN' => $login,
                'EMAIL' => $login.'@email.ru',
                'PASSWORD' => $pass
            ];
            $user = new \CUser;
            $ID = $user->Add($arFields);
            if (intval($ID) > 0)
                \Bitrix\Main\Diag\Debug::writeToFile($ID);
            else
                \Bitrix\Main\Diag\Debug::writeToFile($user->LAST_ERROR);
        }
    }

    private function removeUsers(){
        $result = \Bitrix\Main\UserTable::getList([
            'filter' => ['LOGIN' => array_keys($this->users)]
        ]);

        while($user = $result->fetch()){
            \CUser::Delete($user['ID']);
        }
    }
}
