# VKBOT / Простая библиотека для создания бота
>Библиотека находится на стадии разработки, но уже юзабельна :) 
### Что есть?
  - Обработка команд
  - Работа с кнопками
  - Работа с конфигом
  - Обработка событий

### Что планируется?

  - Рассылка сообщений
  - Загрузка документов
  - Обработка стены сообщества (Получение сообщения с комметариев)

### Примеры

  - [Отправка сообщения по команде]
  - [Отправка клавиатуры и обработка]
  - [Вставка Имя/Фамилия в сообщении]
  - [Работа с конфигом]
  - [Обработка событий]

### Как установить?
Пиши в консоль:
```bash
git clone https://github.com/FunnyRain/vkbot
```
```bash
cd vkbot
```
После, нужно настроить `Main.php` 
```php
$bot = new Control(
    "тут твой супер секретный токен",
    "айди группы (обязательно цифрами)"
);
```
Далее, пиши в консоль
```bash
php Main.php
```
Ну и готово :) твой бот запущен, приятного использования моей библиотеки.

### Примеры использования
###### Простой пример отправки сообщения на команду "info":
```php
require_once __DIR__ . '/autoload.php'; // подключаем библиотеку
$mid = [];
$bot = new Control(
    "токен",
    "айди группы (цифрами)"
);
while (true) {
    $bot->start(); // активируем LongPoll
    $text = $bot->getMessage(); // получаем сообщение
    $message_id = $bot->getMessageId();
    $from_id = $bot->getFromId(); // получаем айди отправителя
    $peer_id = $bot->getPeerId(); // получаем айди переписки
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;
        if ($text == "info") {
            $bot->message->sendMessage("Тебя зовут {fname}", $peer_id, $from_id); // отправляем сообщение
        }
    }
}
```
###### Простой пример отправки клавиатуры и обработка
```php
require_once __DIR__ . '/autoload.php';
$mid = [];
$bot = new Control(
    "токен",
    "айди группы (цифрами)"
);
while (true) {
    $bot->start(); // активируем LongPoll
    $text = $bot->getMessage(); // получаем сообщение
    $message_id = $bot->getMessageId();
    $from_id = $bot->getFromId(); // получаем айди отправителя
    $peer_id = $bot->getPeerId(); // получаем айди переписки
    $payload = $bot->getPayload(); // получаем payload
    $color = new ReflectionClass("Message");
    $color = $color->getConstants();
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;
        if ($text == "keyboard") {
            $bot->message->addKeyboard([
                [$bot->message->addButton(">нажми на меня<", $color['green'], "press_button")]
            ]); // создаём клавиатуру
            $bot->message->sendMessage("нажми на кнопочку :)", $peer_id, $from_id, ["keyboard" => $bot->message->getKeyboard()]);
            // отправляем сообщение с клавиатурой
        } elseif ($payload == "press_button") {
            $bot->message->sendMessage("ты нажал на кнопку", $peer_id, $from_id); // отправляем сообщение
        }
    }
}
```
###### Простой пример вставки Имя / Фамилии в текст сообщения
```php
require_once __DIR__ . '/autoload.php'; // подключаем библиотеку
$mid = [];
$bot = new Control(
    "токен",
    "айди группы (цифрами)"
);
while (true) {
    $bot->start(); // активируем LongPoll
    $text = $bot->getMessage(); // получаем сообщение
    $message_id = $bot->getMessageId();
    $from_id = $bot->getFromId(); // получаем айди отправителя
    $peer_id = $bot->getPeerId(); // получаем айди переписки
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;
        if ($text == "info") {
            $text = "* {fname} - имя
            * {lname} - фамилия
            * {fullname} - имя и фамилия
            * {afname} - имя в виде ссылки  (т.е кликабельное)
            * {alname} - фамилия в виде ссылки (т.е кликабельное)
            * {afullname} - имя и фамилия в виде ссылки (т.е кликабельное)";
            $bot->message->sendMessage($text, $peer_id, $from_id); // отправляем сообщение
        }
    }
}
```
###### Простой пример работы с конфигом
```php
require_once __DIR__ . '/autoload.php'; // подключаем библиотеку
$mid = [];
$bot = new Control(
    "токен",
    "айди группы (цифрами)"
);
while (true) {
    $bot->start(); // активируем LongPoll
    $text = $bot->getMessage(); // получаем сообщение
    $message_id = $bot->getMessageId();
    $from_id = $bot->getFromId(); // получаем айди отправителя
    $peer_id = $bot->getPeerId(); // получаем айди переписки
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;

        /** Работа с конфигом, создание данных пользователя
         * Проверяем папку для хранения данных. Если нету - создаём */
        if (!is_dir(__DIR__ . '/users/'))
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
        } else $cfg = new Config(__DIR__ . '/users/' . $from_id . '.json');

        if ($text == "баланс") {
            /** получаем текущий баланс */
            $bot->message->sendMessage("Твой баланс: " . $cfg->get("money"), $peer_id, $from_id);
        } elseif ($text == "прибавить") {
            /** прибавляем 100 к текущему балансу */
            $cfg->set("money", $cfg->get("money") + 100);
            $cfg->save();
            $bot->message->sendMessage("+100 к балансу", $peer_id, $from_id);
        } elseif ($text == "уменьшить") {
            /** уменьшаем 100 от текущего баланса */
            $cfg->set("money", $cfg->get("money") - 100);
            $cfg->save();
            $bot->message->sendMessage("-100 от балансу", $peer_id, $from_id);
        }
    }
}
```
###### Простой пример использования событий в беседах
```php
require_once __DIR__ . '/autoload.php'; // подключаем библиотеку
$mid = [];
$bot = new Control(
    "токен",
    "айди группы (цифрами)"
);
while (true) {
    $bot->start(); // активируем LongPoll
    $text = $bot->getMessage(); // получаем сообщение
    $message_id = $bot->getMessageId();
    $from_id = $bot->getFromId(); // получаем айди отправителя
    $peer_id = $bot->getPeerId(); // получаем айди переписки
    $action = $bot->getAction(); // получаем событие
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;

        /**
         * Обработка событий (в примере Добавление группы в беседу)
         * https://vk.com/dev/_objects_message (action)
         */
        if ($action["type"] == "chat_invite_user") {
            if ($action["member_id"] == -$bot->group_id) {
                $bot->message->sendMessage("вы добавили бота (меня) в беседу, всем привет :)", $peer_id, $from_id);
            } else {
                $message = "привет, {fname} {lname}! тебя добавили в беседу";
                $replace = $bot->message->replaceNameToMessage($action["member_id"], $message);
                $bot->message->sendMessage($replace, $peer_id, $from_id);
            }
        }

    }
}
```
### Если есть вопросы, пишите [VKontakte](https://vk.com/vyxel)