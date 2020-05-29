<?php

require_once "autoload.php";

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
    $payload = $bot->getPayload();
    $action = $bot->getAction();
    $color = new ReflectionClass("Message");
    $color = $color->getConstants();
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;
        $bot->console->log("{$from_id} => {$text}");
        $bot->debug(1); // debug (0/1)
        ////////////////////////

        /* ТУТ ВАШ КОД */

        /**
         * Обработка событий (в примере Добавление группы в беседу)
         * https://vk.com/dev/_objects_message (action)
         */
        if ($action["type"] == "chat_invite_user") {
            if ($action["member_id"] == -$bot->group_id) {
                $bot->message->sendMessage("вы добавили бота (меня) в беседу, всем привет :)", $peer_id, $from_id);
            } else {
                $message = "привет, {fname} {lname}! тебя добавили в беседу ";
                $replace = $bot->message->replaceNameToMessage($action["member_id"], $message);
                $bot->message->sendMessage($replace, $peer_id, $from_id);
            }
        }

        /**
         * Обработка payload (нажатие на кнопку)
         */
        if ($payload == "red-button") {
            $bot->message->sendMessage("нажата красная кнопка", $peer_id, $from_id);
        } elseif ($payload == "green-button") {
            $bot->message->sendMessage("нажата зеленая кнопка", $peer_id, $from_id);
        } elseif ($payload == "blue-button") {
            $bot->message->sendMessage("нажата голубая кнопка", $peer_id, $from_id);
        }

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
                    [$bot->message->addButton("белая"), $bot->message->addButton("красная", $color['red'], "red-button")],
                    [$bot->message->addButton("зеленая", $color['green'], "green-button"), $bot->message->addButton("голубая", $color['blue'], "blue-button")],
                    [$bot->message->addButtonLink("ссылка", "https://vk.com/id1")]
                ]);
                $bot->message->sendMessage("все кнопочки", $peer_id, $from_id, ["keyboard" => $bot->message->getKeyboard()]);
                break;
            case "remove":
                $bot->message->sendMessage("кнопки удалены", $peer_id, $from_id, ["keyboard" => $bot->message->remKeyboard()]);
                break;
            default:
                /**
                 * неизвестная команда
                 */
                if (!$payload and !empty($text))
                    $bot->message->sendMessage("{fname}, такой команды не существует!", $peer_id, $from_id);
                break;
        }

        ////////////////////////
    }
}