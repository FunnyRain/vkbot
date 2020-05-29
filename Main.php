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
    $text = $bot->getMessage();
    $from_id = $bot->getFromId();
    $peer_id = $bot->getPeerId();
    $message_id = $bot->getMessageId();
    $attachments = $bot->getAttachment();
    $color = new ReflectionClass("Message");
    $color = $color->getConstants();
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;
        $bot->console->log("{$from_id} => {$text}");
        // $bot->debug();
        ////////////////////////

        /* ТУТ ВАШ КОД */
        $msg = explode(" ", mb_strtolower($text));
        switch ($msg[0]) {
            case "q":
                /**
                 * простое приветствие
                 */
                $bot->message->sendMessage("{fname}, привет!", $peer_id, $from_id);
                break;
            case "keyboard":
                /**
                 * при вызове команды, создаются все виды кнопок
                 */
                $bot->message->addKeyboard([
                    [$bot->message->addButton("белая"), $bot->message->addButton("красная", $color['red'])],
                    [$bot->message->addButton("зеленая", $color['green']), $bot->message->addButton("голубая", $color['blue'])],
                    [$bot->message->addButtonLink("ссылка", "https://vk.com/id1")]
                ]);
                $bot->message->sendMessage("все кнопочки", $peer_id, $from_id, ["keyboard" => $bot->message->getKeyboard()]);
                break;
            default:
                /**
                 * неизвестная команда
                 */
                $bot->message->sendMessage("{fname}, такой команды не существует!", $peer_id, $from_id);
                break;
        }

        ////////////////////////
    }
}