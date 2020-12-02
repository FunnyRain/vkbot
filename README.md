# VKBOT / Простая библиотека для создания бота
>Прошлую версию библиотеки с полной документацией можно скачать тут [Releases](https://github.com/FunnyRain/vkbot/releases) :grin:

### Что есть?
  - Bots LongPoll API
  - Обработка команд
  - Обработка событий
  - Работа с кнопками

### Что планируется?

  - Загрузка документов
  - Рассылка сообщений
  - Создание виджета
  - Сайт с документацией

### Примеры
  - [Отправка сообщения по команде](https://github.com/FunnyRain/vkbot#%D0%BF%D1%80%D0%BE%D1%81%D1%82%D0%BE%D0%B9-%D0%BF%D1%80%D0%B8%D0%BC%D0%B5%D1%80-%D0%BE%D1%82%D0%BF%D1%80%D0%B0%D0%B2%D0%BA%D0%B8-%D1%81%D0%BE%D0%BE%D0%B1%D1%89%D0%B5%D0%BD%D0%B8%D1%8F-%D0%BD%D0%B0-%D0%BA%D0%BE%D0%BC%D0%B0%D0%BD%D0%B4%D1%83-info)

### Как установить?
   - bbbbb

### Примеры использования
###### Добавление клавиатуры / Вызов по команде "кнопки":
```php
<?php require_once 'autoload.php';

$bot = new Bot();
$bot->setToken('токен');

$bot->start(function($data)use($bot){

    $msg = $bot->getMessage();
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
          ],
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
### Если есть вопросы, пишите [VKontakte](https://vk.com/vyxel)