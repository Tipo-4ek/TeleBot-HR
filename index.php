<?php
include('vendor/autoload.php');
use Telegram\Bot\Api;

//Блок с данными БД и API ключа Bot Telegram
$db = new PDO('mysql:dbname=***dbname***;host=localhost', '***login***', '***password***');
$telegram = new Api('');


$message = $telegram->getWebhookUpdates()->getMessage();

$text = $message->getText(); // Текст присланный пользователем
$chat = $message->getChat()->getId(); // id чата с пользователем
$username = $message->getFrom()->getUsername(); // username пользователя

//Проверяем позицию пользователя по анкете
$sth = $db->prepare('SELECT * FROM users WHERE chatid=?');
$sth->execute(array($chat));
$array = $sth->fetch();

$state = $array['state'];


//Создаем чат с именем chatid.txt, где будем запоминать все сообщения на анкету текущего пользователя
file_put_contents($chat.'.txt', $text . PHP_EOL, FILE_APPEND);

if ($sth->rowCount() === 0){ //Если в базе не найдена запись такого chatid, то создаем новую
    $state = '0';
    $sql = "INSERT INTO users (chatid,state) VALUES (?,?)";
    $db->prepare($sql)->execute([$chat,$state]);
}

//Массив для вопросов
$data = [
'0' => 'фио',
'1' => 'Пол',
'2' => 'Возраст',
'3' => 'Должность',
'4' => 'ЗП',
'5' => 'Укажите свои контакты (Номер телефона, e-mail, соц.сети)',
'6' => 'Спасибо за заполнение анкеты, менеджер свяжется с Вами',
];

//Если человек не на крайних стадиях заполнения анкеты, то продолжаем ему задавать вопросы
   if (($state > 0) and ($state < 7)){
                $text = '/anketa';
   }
   

if(!empty($text)){
    switch($text){
        case '/start':
            $message = 'Вас приветствует Telebot by Tipo_4ek! Для вывода списка доступных команд введите /help';
            $telegram->sendMessage([ 'chat_id' => $chat, 'text' => $message ]);
            break;
        case '/help':
            $message = 'Список доступных команд:
/start - начало работы с ботом
/help - выводит данный список
/anketa - начать прохождение анкеты (Ответы на другие вопросы не предполагаются)';
            $telegram->sendMessage([ 'chat_id' => $chat, 'text' => $message ]);
            break;


       case '/anketa':
              //Отправляем ему вопрос на нужном этапе
              $message2 = $data[$state];
              $state = $state + 1;
              $telegram->sendMessage([ 'chat_id' => $chat, 'text' => $message2 ]);
              
              //Обновляем позицию человека в базе, чтобы "не забыть" пользователя
              $sql = "UPDATE users SET state=? WHERE chatid=?"; 
              $db->prepare($sql)->execute([$state, $chat]);


             //Выполняем ряд действий, когда пользователь заполнил анкету
             if ($state > 6){
                $anketa_data = file($chat.'.txt'); 
                for ($i=0; $i<=6; $i++){
                    file_put_contents($chat.'_FINAL.txt', $anketa_data[$i+1] . PHP_EOL, FILE_APPEND); //Создаем файл chatid_FINAL.txt с данными из его анкеты
                }    

                $sql = "UPDATE users SET state=? WHERE chatid=?"; //Обнуляем позицию человека, чтобы пользователь смог пройти анкету повторно
                $db->prepare($sql)->execute(['0', $chat]);     

                //Подключаем свою функцию отправки письма на почту    
             }
              break;

        default: 
            $telegram->sendMessage([ 'chat_id' => $chat, 'text' => 'Команда не распознана. Для помощи введите /help' ]);
            break;
    }
}
else{
    $telegram->sendMessage([ 'chat_id' => $chat, 'text' => 'Для работы с ботом пришлите текстовое сообщение!' ]);
}
