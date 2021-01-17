<?php

class Messages {

    public $bot;

    public function __construct(Bot $bot) {
        $this->bot = $bot;
    }

    /**
     * Получение и Сравнение сообщения
     * @param array|string $object
     * @return string
     */
    public function get($object = []): string {
        if (!is_array($object)) {
            if (substr_count($object, '/') === 2) {
                return preg_match($object, (isset($this->bot->vkdata['text'])) ? $this->bot->vkdata['text'] : "");
            } else {
                if (isset($this->bot->vkdata['text']) and $this->bot->vkdata['text'] == $object)
                    return true;
                else
                    return false;
            }
        }

        if (empty($object)) $object = $this->bot->vkdata;
        if (isset($object['text']))
            return $object['text'];
        else
            return "";
    }

    /**
     * Получение Payload
     * @return string   Вернет payload
     */
    public function getPayload($object = []): string {
        if (empty($object)) $object = $this->bot->vkdata;
        if (isset($object['payload']))
            return $object['payload'];
        else
            return "";
    }

    /**
     * Получение цыферного айди пользователя
     * @param array $object
     * @return integer
     */
    public function getUserId(array $object = []): int {
        if (empty($object)) $object = $this->bot->vkdata;
        return (isset($object['from_id'])) ? $object['from_id'] : 1;
    }

    /**
     * Получение цыферного айди беседы
     * @param array $object
     * @return integer
     */
    public function getChatId(array $object = []): int {
        if (empty($object)) $object = $this->bot->vkdata;
        return (isset($object['peer_id'])) ? $object['peer_id'] : Bot::PEER_ID + 1;
    }

    /**
     * Быстрый ответ сообщением
     * @param string $text  Сообщение
     * @param array $args   Дополнительные параметры
     * @return array        Вернёт айди сообщения
     */
    public function reply(string $text, array $args = []) {
        if (!isset($text) and !isset($args['attachment']))
            return $this->bot->getLog()->error('Не указан текст!');

        $return = $this->bot->api('messages.send', [
            'random_id' => rand(),
            'peer_ids' => isset($args['peer_ids']) ? $args['peer_ids'] : $this->bot->vkdata['peer_id'],
            'message' => $text,
            'content_source' => json_encode([
                'type' => 'message',
                'owner_id' => $this->getUserId(),
                'peer_id' => isset($args['peer_id']) ? $args['peer_id'] : $this->bot->vkdata['peer_id'],
                'conversation_message_id' => $this->bot->vkdata['conversation_message_id']
            ], JSON_UNESCAPED_UNICODE)
        ] + $args);

        return $return;
    }

    /**
     * Отправка стикера отдельным сообщением
     * @param integer $id       Айди стикера
     * @param integer $peer_id  Айди получателя (Необязательно)
     * @return array            Вернёт айди сообщения
     */
    public function sendSticker(int $id = 0, int $peer_id = 1): array {
        return $this->bot->api('messages.send', [
            'random_id' => rand(),
            'peer_id' => ($peer_id == 1) ? $this->bot->vkdata['peer_id'] : $peer_id,
            'sticker_id' => $id
        ]);
    }

    /**
     * Отправка сообщения
     * @param string $text      Текст сообщения
     * @param string|integer $peer_ids  Айди получател(я/ей) (Перечислять через запятую)
     * @param array $args       Дополнительные параметры
     * @return array            Вернёт айди сообщения
     */
    public function sendMessage(string $text, $peer_ids, array $args = []) {
        if (!isset($text) and !isset($args['attachment']))
            return $this->bot->getLog()->error('Не указан текст!');
        if (preg_match('/(fname|lname|afname|alname|fullname|afullname)/ui', $text))
            $text = $this->replaceNameToMessage(isset($args['from_id']) ? $args['from_id'] : $this->bot->vkdata['from_id'], $text);

        $return = $this->bot->api('messages.send', [
            'random_id' => rand(),
            'peer_ids' => isset($peer_ids) ? $peer_ids : $this->bot->vkdata['peer_id'],
            'message' => $text,
            'content_source' => json_encode([
                'type' => 'message',
                'owner_id' => $this->getUserId(),
                'peer_id' => isset($args['peer_id']) ? $args['peer_id'] : $this->bot->vkdata['peer_id'],
                'conversation_message_id' => $this->bot->vkdata['conversation_message_id']
            ], JSON_UNESCAPED_UNICODE)
        ] + $args);

        return $return;
    }

    /**
     * Заменяет ключевые слова на Имя\Фамилию пользователя
     * @param integer $user_id  Айди пользователя
     * @param string $text      Текст с ключевыми словами
     * @return string           Готовый текст
     */
    public function replaceNameToMessage(int $user_id, string $text): string {
        return str_replace(
            ["{fname}", "{lname}", "{afname}", "{alname}", "{fullname}", "{afullname}"],
            [
                $this->getInfo($user_id)['first_name'],
                $this->getInfo($user_id)['last_name'],
                "[id{$user_id}|" . $this->getInfo($user_id)['first_name'] . "]",
                "[id{$user_id}|" . $this->getInfo($user_id)['last_name'] . "]",
                $this->getInfo($user_id)['first_name'] . " " . $this->getInfo($user_id)['last_name'],
                "[id{$user_id}|" . $this->getInfo($user_id)['first_name'] . " " . $this->getInfo($user_id)['last_name'] . "]",
            ],
            $text
        );
    }

    public function edit(string $text, int $conversation_message_id, array $args) {
        if (!isset($text) or !isset($conversation_message_id))
            return $this->bot->getLog()->error('Не хватает параметров!');

        $return = $this->bot->api('messages.edit', [
            'peer_id' => isset($args['peer_id']) ? $args['peer_id'] : $this->bot->vkdata['peer_id'],
            'message' => $text,
            'conversation_message_id' => $conversation_message_id
        ] + $args);

        return $return;
    }

    public function sendEventAnswer(string $event_id, array $event_data = [], array $args) {
        if (!isset($event_id) or !isset($event_data))
            return $this->bot->getLog()->error('Не хватает параметров!');

        $return = $this->bot->api('messages.sendMessageEventAnswer', [
            'event_id' => $event_id,
            'user_id' => isset($args['user_id']) ? $args['user_id'] : $this->bot->vkdata['user_id'],
            'peer_id' => isset($args['peer_id']) ? $args['peer_id'] : $this->bot->vkdata['peer_id'],
            'event_data' => $event_data
        ] + $args);

        return $return;
    }

    public function getInfo(int $user_id, string $name_case = ""): array {
        return $this->bot->api("users.get", ["user_ids" => $user_id, "name_case" => $name_case])[0];
    }
}
