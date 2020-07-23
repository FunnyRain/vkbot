# Все существующие методы
### Bots LongPoll API (Бот для группы)
```php
// подключение к библиотеке (Bots LongPoll API)
$bot = new Control(
    "токен группы", // обязательный параметр
    "айди группы (без знака «минус»)", // обязательный параметр (целое число)
    5.102, // необязательный параметр | версия VK API
    "токен страницы" // необязательный параметр | если не используется вызов метода с участием токена пользователя
);
```
### User LongPoll API (Бот для страницы)
```php
// подключение к библиотеке (User LongPoll API)
$bot = new Control(
    "токен страницы", // обязательный параметр
    0, // оставляем 0
    5.102, // необязательный параметр | версия VK API
);
```
##### Получение.. 
[ C - поддерживается CallBack, U - поддержка User LongPoll, L - поддержка Bots LongPoll]    
[C\U\L] ```$bot->getMessage()``` - Текст сообщения  
[C\U\L] ```$bot->getFromId()``` - id пользователя   
[C\U\L] ```$bot->getPeerId()``` - id чата \ беседы      
[C\U\L] ```$bot->getMessageId()``` - id сообщения       
[C\U\L] ```$bot->getAttachment()``` - Прикрепленные файлы         
[C\L] ```$bot->getPayload()``` - Дополнительная информация о кнопке         
[C\L] ```$bot->getAction()``` - События ( Подробнее: https://vk.com/dev/groups_events )     
CallBack и User LongPoll API не работает с получением данных из комментариев! Это работает только на LongPoll     
[L] ```$bot->wall->getMessage()``` - Сообщение комментария      
[L] ```$bot->wall->getPostId()``` - id поста      
[L] ```$bot->wall->getCommentId()``` - id комментария      
## Работа с сообщениями
#### Вызывать: $bot->message->(метод)
##### Отправка сообщения
###### Дополнительно - https://vk.com/dev/messages.send
```$message``` - Текст сообщения         
```$peer_id``` - id беседы \ чата         
```$from_id``` - id получателя         
```$params``` - Дополнительные параметры         
```php
sendMessage(string $message = "", int $peer_id = null, int $from_id = null, array $params = [])
```
##### Получение имя\фамилия пользователя
```$uid``` - id пользователя         
```$message``` - Текст сообщения         
```php
replaceNameToMessage(int $uid, string $message)
```
##### Информация о пользователе
```$user_id``` - id пользователя         
```$name_case``` - Склонение по падежам [nom - именительный, gen - родительный, dat - дательный, acc - винительный, ins - творительный, abl - предложный. По умолчанию nom.]         
```php
getInfo(int $user_id, string $name_case = "")
```
после выполнения, результат будет таков:
```json
{
    "id": 1,
    "first_name": "Павел",
    "last_name": "Дуров",
    "is_closed": false,
    "can_access_closed": true
}
```
##### Добавление клавиатуры
```$keyboard``` - Массив с кнопками (см. ```addButton```)         
```$one_time``` - Скрывать ли клавиатуру после первого использования (true \ false)         
```$inline``` - Должна ли клавиатура отображаться внутри сообщения (true \ false)         
```php
addKeyboard(array $keyboard = [], bool $one_time = false, bool $inline = false)
```
##### Удаление клавиатуры
```php
remKeyboard()
```
##### Добавление кнопки
###### Дополнительно - https://vk.com/dev/bots_docs_3?f=4.%2B%D0%9A%D0%BB%D0%B0%D0%B2%D0%B8%D0%B0%D1%82%D1%83%D1%80%D1%8B%2B%D0%B4%D0%BB%D1%8F%2B%D0%B1%D0%BE%D1%82%D0%BE%D0%B2
```$text``` - Текст кнопки         
```$color``` - Цвет кнопки [красный - 'negative', зелёный - 'positive, белый - 'default', синий - 'primary'. По умолчания белый]         
```$payload``` - Дополнительная информация         
```php
addButton(string $text, string $color = self::white, string $payload = "")
```
пример:
```php
$bot->message->addKeyboard([
    [
        $bot->message->addButton(">нажми на меня<", 'positive', "press_button1"),
        $bot->message->addButton(">или на меня<", 'negative', "press_button2"),
    ]
]);
```
##### Кнопка с ссылкой
```$text``` - Текст кнопки         
```$link``` - Ссылка         
```php
addButtonLink(string $text, $link = null)
```
##### Отправка клавиатуры
```php
getKeyboard()
```
пример:
```php
$bot->message->sendMessage("нажми на кнопочку :)", $peer_id, $from_id, ["keyboard" => $bot->message->getKeyboard()]);
```
##### Загрузка фотографии из локальной директории
```$src``` - Путь до фотографии         
```php
uploadPhoto(string $src)
```
##### Загрузка документа из локальной директории
```$src``` - Путь до документа         
```php
uploadDoc(string $src)
```
## Работа со стеной сообщества
#### Вызывать: $bot->wall->(метод)
##### Отправка комментария
###### Дополнительно - https://vk.com/dev/wall.createComment
```$message``` - Текст комментария         
```$params``` - Дополнительные параметры         
```php
sendComment(string $message = "", array $params = [])
```
##### Создание поста
###### Дополнительно - https://vk.com/dev/wall.post
```$message``` - Текст         
```$params``` - Дополнительные параметры         
```php
addPost(string $message, array $params = [])
```
##### Загрузка фотографии из локальной директории на стену сообщества
```$src``` - Путь до фотографии         
```php
uploadWallPhoto(string $src)
```