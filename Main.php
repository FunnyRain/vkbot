<?php

require_once "autoload.php";

ini_set('display_errors', 1);
date_default_timezone_set('Europe/Moscow');

$mid = [];
$bot = new Control(
    "токен",
    "айди группы"
);

while (true) {
    $bot->start();
    $text = isset($bot->get["text"]) ? $bot->get["text"] : null;
    $from_id = isset($bot->get["from_id"]) ? $bot->get["from_id"] : null;
    $peer_id = isset($bot->get["peer_id"]) ? $bot->get["peer_id"] : null;
    $message_id = isset($bot->get["conversation_message_id"]) ? $bot->get["conversation_message_id"] : null;
    $attachments = isset($bot->get["attachments"]) ? $bot->get["attachments"] : null;
    $color = new ReflectionClass("Message");
    $color = $color->getConstants();
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;
        $bot->console->log("{$from_id} => {$text}");
        $bot->debug();
        ////////////////////////
        /* ТУТ ВАШ КОД */

        // пример создания кнопки
        $bot->message->addKeyboard(array(
            [$bot->message->addButton("kb1", $color['red']), $bot->message->addButton("kb2", $color['white'])],
            [$bot->message->addButton("kb2", $color['green']), $bot->message->addButton("kb4", $color['blue'])]
        ));
        // пример отправки сообщения с кнопкой
        $bot->message->sendMessage("{fname} {lname} Кнопка отправлена!", $peer_id, ["keyboard" => $bot->message->getKeyboard(), "uid" => $from_id]);
        // на данный момент, параметр "uid" => $from_id нужен для работоспособности бота в беседе, скоро исправлю!
        ////////////////////////
    }
}