<?php

namespace req;

require_once 'DbLayer.php';

use DbLayer\DbLayer;

class UserInteraction
{
    private $allData;
    private $userId;
    private $userDbInfo;
    private $userReqData;
    private $db;

    /*JSON Data input*/
    function __construct($data)
    {
        $this->allData = $data;
        $this->userId = $data['session']['user_id'];
        $this->db = new DbLayer('root', '9e4ed01e02', '127.0.0.1', 'alisa');
    }

    public function start(){

        if (isset($this->allData['request']['payload']))
        {

            $payload = json_decode($this->allData['request']['payload'], true);
            $this->db->update(array_keys( $payload )[0], array_values($payload)[0], $this->userId);

        }


        $this->isUserExist();

    }

    private function isUserExist()
    {
        try{

            $userInfo =  $this->db->select($this->userId);

        } catch (\Exception $e){

            $this->db->insertNewUserId($this->userId);
            $userInfo =  $this->db->select($this->userId);

        }

        $this->userDbInfo = $userInfo;

        if (count(array_filter($userInfo)) == count($userInfo)) { //если все не null

            echo 'showButton';//showButton();

        } else {

            $this->makeRegister($userInfo);//передаем сюда результат селект

        }

    }

    private function makeRegister($userInfo)
    { //здесь свитч для того чтобы определить на каком ты этапе


        switch (null) {


//            case $userInfo['userId']:
//                $this->askQuestion('userId'); //TODO сделать функцию создания userId
            case $userInfo['edForm']:
                $this->askQuestion('edForm');
                break;
            case $userInfo['inst']:
                $this->askQuestion('fac');
                break;
            case $userInfo['fac']:
                $this->askQuestion('fac');
                break;
            case $userInfo['curs']:
                $this->askQuestion('curs');
                break;
            case $userInfo['groupId']:
                $this->askQuestion('groupId');
                break;


        }



    }

    private function makeButton($data, $text = ' ')
    {
        $buttons = '';


        for ($i = 0; $i < count($data['data']); $i++) {

            $buttons = $buttons . '        {
            "title": "' . $data['data'][$i]['name'] . '",
            "payload": "[' .  $data['data'][$i]['name'] . ' : ' . $data['data'][$i]['id'] . ']",
            "hide": true
        },';

        }

        $buttons = substr($buttons, 0, strlen($buttons) - 1);

        echo $response = '{
  "response": {
    "text": "' . $text . '",
    "tts": "' . $text . '",
    "buttons": [
              ' . $buttons . '
    ],
    "end_session": false
  },
  "session": {
    "session_id": "2eac4854-fce721f3-b845abba-20d60",
    "message_id": 4,
    "user_id": "AC9WC3DF6FCE052E45A4566A48E6B7193774B84814CE49A922E163B8B29881DC"
  },
  "version": "1.0"
}';


    }

    private function askQuestion($question)
    {
        switch ($question) {


            case 'edForm':

                $apiData = json_decode(file_get_contents('https://bot-srv.mgsu.ru/api/get/grade'), true);
                $this->makeButton($apiData,  'Выберите форму обучения: ');


                break;
            case 'inst':

                $apiData = json_decode(file_get_contents('https://bot-srv.mgsu.ru/api/get/institute?edForm=' . $this->userDbInfo['edForm']), true);
                $this->makeButton($apiData,  'Выберите институт: ');

                break;
            case 'fac':

                $apiData = json_decode(file_get_contents('http://bot-srv.mgsu.ru/api/get/faculty?instituteId=' . $this->userDbInfo['fac']), true);
                $this->makeButton($apiData,  'Выберите факультут: ');

                break;
            case 'curs':

                $apiData = json_decode(file_get_contents('http://bot-srv.mgsu.ru/api/get/year?facultyId=' . $this->userDbInfo['curs']), true);
                $this->makeButton($apiData,  'Выберите курс: ');

                break;
            case 'groupId':

                $apiData = json_decode(file_get_contents('http://bot-srv.mgsu.ru/api/get/party?yearId=' . $this->userDbInfo['curs']), true);
                $this->makeButton($apiData,  'Выберите вашу группу: ');

                break;



        }

    }

}