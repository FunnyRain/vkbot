<?php

require_once "autoload.php";

$bot = new Control(
    "токен",
    "айди группы (цифрами)"
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

    $bot->console->log("{$from_id} => {$text}");
    $bot->debug(1); // debug (0/1)
    ////////////////////////

    /* ТУТ ВАШ КОД */

    /** Работа с конфигом, создание данных пользователя
     * Проверяем папку для хранения данных. Если нету - создаём */
    if (@!is_dir(__DIR__ . '/users/'))
        @mkdir(__DIR__ . '/users/');
    /**  Проверяем наличие аккаунта. Если нету - создаём */
    if (!file_exists(__DIR__ . '/users/' . $from_id . '.json')) {
        /** Назначаем данные,
         * id - id пользователя
         * money - баланс */
        $cfg = new Config(__DIR__ . '/users/' . $from_id . '.json', Config::JSON, [
            'id' => $from_id,
            'money' => 1000
        ]);
        $bot->message->sendMessage("аккаунт создан", $peer_id, $from_id);
    }

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

    /** Обработка payload (нажатие на кнопку) */
    if ($payload == "red-button") {
        $bot->message->sendMessage("нажата красная кнопка", $peer_id, $from_id);
    } elseif ($payload == "green-button") {
        $bot->message->sendMessage("нажата зеленая кнопка", $peer_id, $from_id);
    } elseif ($payload == "blue-button") {
        $bot->message->sendMessage("нажата голубая кнопка", $peer_id, $from_id);
    }

    $cfg = new Config(__DIR__ . '/users/' . $from_id . '.json', Config::JSON);
    $msg = explode(" ", mb_strtolower($text));
    switch ($msg[0]) {
            /** Работа с конфигом */
        case "баланс":
            /** получаем текущий баланс */
            $bot->message->sendMessage("Твой баланс: " . $cfg->get("money"), $peer_id, $from_id);
            break;
        case "прибавить":
            /** прибавляем 100 к текущему балансу */
            $cfg->set("money", $cfg->get("money") + 100);
            $cfg->save();
            $bot->message->sendMessage("+100 к балансу", $peer_id, $from_id);
            break;
        case "уменьшить":
            /** уменьшаем 100 от текущего баланса */
            $cfg->set("money", $cfg->get("money") - 100);
            $cfg->save();
            $bot->message->sendMessage("-100 от баланса", $peer_id, $from_id);
            break;
            /** Конец работы с конфигом */
        case "photo":
            /** загрузка фотографии из директории */
            $bot->message->sendMessage("1 фотография", $peer_id, $from_id, [
                "attachment" => $bot->message->uploadPhoto(__DIR__ . '/test.jpeg')
            ]);

            /** загрузка нескольких фотографий из директории */
            $bot->message->sendMessage("3 фотографии", $peer_id, $from_id, [
                "attachment" => [
                    $bot->message->uploadPhoto(__DIR__ . '/test.jpeg'),
                    $bot->message->uploadPhoto(__DIR__ . '/test.jpeg'),
                    $bot->message->uploadPhoto(__DIR__ . '/test.jpeg')
                ]
            ]);
            break;
        case "doc":
            /** загрузка фотографии и документа из директории */
            $bot->message->sendMessage("3 фотографии", $peer_id, $from_id, [
                "attachment" => [
                    $bot->message->uploadPhoto(__DIR__ . '/test.jpeg'),
                    $bot->message->uploadDoc(__DIR__ . '/test.jpeg')
                ]
            ]);
            break;
        case "q":
            /** простое приветствие */
            $bot->message->sendMessage("{fname}, привет!", $peer_id, $from_id);
            break;
        case "keyboard":
            /** при вызове команды, создаются все виды кнопок */
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
            /** неизвестная команда */
            if (!$payload and !empty($text))
                $bot->message->sendMessage("{fname}, такой команды не существует!", $peer_id, $from_id);
            break;
    }

    ////////////////////////
}