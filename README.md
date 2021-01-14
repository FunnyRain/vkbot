# VKBOT / Простая библиотека для создания бота
>Прошлую версию библиотеки с полной документацией можно скачать тут [Releases](https://github.com/FunnyRain/vkbot/releases) :grin:

## Документация
* [https://funnyrain.gitbook.io/vkbot/](https://funnyrain.gitbook.io/vkbot/)
## Что есть?
  - Bots LongPoll API
  - Обработка команд
  - Обработка событий
  - Работа с кнопками
  - Загрузка документов

## Что планируется?

  - Рассылка сообщений
  - Создание виджета

## Примеры использования
###### Добавление клавиатуры / Вызов по команде "кнопки":
```php
<?php require_once 'autoload.php';

$bot = new Bot();
$bot->setToken('токен');

$bot->start(function($data)use($bot){

    $msg = $bot->getMessage();
    $kb = $bot->kBuilder(); // Подключаем билдера кнопок
    if ($msg->get() == "кнопки") {
        $kb->create(
          [
            [ // <-- Начало первой строки
              $kb->button('красная кнопка', 'red'),
              $kb->button('зеленая кнопка', 'green'),
              $kb->button('синяя кнопка', 'blue')
            ], // <-- Конец первой строки 
            [ // <-- Начало второй строки
              $kb->link('кнопка с ссылкой', 'http://example.com'),
              $kb->location()
            ] // <-- Конец второй строки 
          ]
          // one_time (По стандарту false),
          // inline (По стандарту false)
        );
        /** 
         * Должно вывести клавиатуру в таком виде:
         *        [--] [--] [--]
         *          [--] [--]
         */
        $msg->reply('Отправляю клавиатуру:', [
          'keyboard' => $kb->get()
        ]);
    }

});
```
###### Простой обработки события "Приглашение бота в беседу":
```php
<?php require_once 'autoload.php';

$bot = new Bot();
$bot->setToken('токен');

$bot->start(function($data)use($bot){

    // chat_invite_user - Событие добавления в беседу
    // Список всех событий: https://vk.com/dev/groups_events
    $bot->isAction('chat_invite_user', function($data)use($bot) {
        $msg = $bot->getMessage();
        if ($data['member_id'] == -$bot->group_id)
            $msg->reply('спасибо за приглашение');
    });

});
```
###### Простой пример отправки сообщения на команду "info":
```php
<?php require_once 'autoload.php';

$bot = new Bot();
$bot->setToken('токен');

$bot->start(function($data)use($bot){

    $msg = $bot->getMessage();
    if ($msg->get() == "info") {
        $msg->reply(
            "привет"
        );
        //$msg->sendSticker(51077);
    }

});
```
## Если есть вопросы, пишите [VKontakte](https://vk.com/vyxel)
