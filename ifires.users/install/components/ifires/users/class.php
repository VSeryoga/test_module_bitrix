<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class ExampleCompSimple extends CBitrixComponent {

    /**Проверка есть такой логин - пароль
     * @param mixed $login
     * @param mixed $password
     * 
     * @return [type]
     */
    private function checkUser($login, $password)
    {
        $user = \Bitrix\Main\UserTable::getList([
            'select' => ['ID', "PASSWORD"],
            'filter' => ['LOGIN' => $login]
        ])->fetch();
        if(\Bitrix\Main\Security\Password::equals($user['PASSWORD'], $password)){
            $this->updateLastLogin($user['ID']);
            return true;
        }else{
            return false;
        }
        $this->arResult['USER'] = $user;
    }

    /**Обновление даты последнего обращения
     * @param mixed $id
     * 
     * @return [type]
     */
    private function updateLastLogin($id){
        $date = new \DateTime();
        $arFields = ['LAST_LOGIN' => date('d.m.Y H:i:m')];
        $user = new \CUser;
        $user->Update($id, $arFields);
    }

   
    /**Поиск пользователя по фио
     * @param string $fio
     * 
     * @return [type]
     */
    private function search($fio)
    {
        $res = \Ifires\Users\UserTable::getList([
            'filter' => ['FIO' => '%'.$fio.'%'],
            'select' => ['*', 'POST_FIELD_' => 'POST', 'OFFICE_FIELD' => 'OFFICE']
        ]);
        while($user = $res->fetch()){
            $result[] = [
                $user['ID'],
                $user['FIO'],
                $user['POST_FIELD_NAME'],
                $user['PHONE'],
                $user['OFFICE_FIELDNAME'],
                $user['OFFICE_FIELDADDRESS'],
            ];
        }
        return $result;
    }

    /**Получение офисов
     * @return [type]
     */
    private function getOffices(){
        $res = \Ifires\Users\OfficeTable::getList()->fetchAll();
        return $res;
    }

    /**Изменение пользователя
     * @param mixed $data
     * 
     * @return [type]
     */
    private function update($data){
        $arFields = [];
        if($data['fio']){
            //ИД должности
            $arPost = \Ifires\Users\PostTable::getList([
                'filter' => ['NAME' => $data['post']]
                ])->fetch();
            $arFields['POST_ID'] = $arPost['ID'];
        }

        if($data['phone']){
            $arFields['PHONE'] = $data['phone'];
        }

        if($data['fio']){
            $arFields['FIO'] = $data['fio'];
        }

        $res = \Ifires\Users\UserTable::update($data['id'], $arFields);
        
        return $res;
    }

    /**Формирование ответа
     * @param mixed $status
     * @param string $status_message
     * @param string $out
     * 
     * @return [type]
     */
    private function result($status, $status_message = '', $out = ''){
        $this->arResult['STATUS_ANSWER'] = $status;
        $this->arResult['STATUS_MESSAGE'] = $status_message;
        $this->arResult['OUT'] = $out;
    }



    public function executeComponent() {
        Loader::includeModule('ifires.users');
        $getParams = Application::getInstance()->getContext()->getRequest()->getQueryList()->toArray();

        if($getParams['login'] && $getParams['passwd']){
            if($this->checkUser($getParams['login'], $getParams['passwd'])){
                switch ($getParams['method']) {
                    case 'search':
                        $result = $this->search($getParams['fio']);
                        if($result){
                            $this->result('ok', '', $result);
                        }else{
                            $this->result('error', 'Совпадений не найдено');
                        }
                        break;
                    
                    case 'offices':
                        $result = $this->getOffices();
                        if($result){
                            $this->result('ok', '', $result);
                        }else{
                            $this->result('error');
                        }
                    break;
                    case 'update':
                        $result = $this->update($getParams);
                        if($result->isSuccess()){
                            $this->result('ok');
                        }else{
                            $this->result('error', $result->getErrorMessages());
                        }
                    break;
                }
            }else{
                $this->result('error', 'Ошибка авторизации');
            }
        }

        $this->includeComponentTemplate();
    }
}