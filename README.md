# VKBOT / Простая библиотека для создания бота
>Прошлую версию библиотеки с полной документацией можно скачать тут [Releases](https://github.com/FunnyRain/vkbot/releases) :grin:

### Что есть?
  - Bots LongPoll API
  - Обработка команд

### Что планируется?

  - Работа с кнопками
  - Обработка событий
  - Загрузка документов
  - Рассылка сообщений
  - Создание виджета
  - Сайт с документацией

### Примеры
  - [Отправка сообщения по команде](https://github.com/FunnyRain/vkbot#%D0%BF%D1%80%D0%BE%D1%81%D1%82%D0%BE%D0%B9-%D0%BF%D1%80%D0%B8%D0%BC%D0%B5%D1%80-%D0%BE%D1%82%D0%BF%D1%80%D0%B0%D0%B2%D0%BA%D0%B8-%D1%81%D0%BE%D0%BE%D0%B1%D1%89%D0%B5%D0%BD%D0%B8%D1%8F-%D0%BD%D0%B0-%D0%BA%D0%BE%D0%BC%D0%B0%D0%BD%D0%B4%D1%83-info)

### Как установить?
   - bbbbb

### Примеры использования
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