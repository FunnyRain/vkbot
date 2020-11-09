<?php

class Messages {

    public Bot $bot;

    public function __construct(Bot $bot) {
        $this->bot = $bot;
    }

    /** 
     * (
    [date] => 1604851861
    [from_id] => 439239695
    [id] => 3098
    [out] => 0
    [peer_id] => 439239695
    [text] => f
    [conversation_message_id] => 1543
    [fwd_messages] => Array
        (
        )

    [important] => 
    [random_id] => 0
    [attachments] => Array
        (
        )

    [is_hidden] => 
)
    */

    /**
     * Получение сообщения
     * @param array $object
     * @return string
     */
    public function get(array $object = []): string {
        if (empty($object)) $object = $this->bot->vkdata;
        if (isset($object['text']))
            return $object['text'];
    }

    /**
     * Быстрый ответ сообщением
     * @param string $text  Сообщение
     * @param array $args   Дополнительные параметры
     * @return array        Вернёт айди сообщения
     */
    public function reply(string $text, array $args = []): array {
        if (!isset($text) and !isset($args['attachment']))
            return $this->bot->getLog()->error('Не указан текст!');
        
        return $this->bot->api('messages.send', [
            'random_id' => rand(),
            'peer_id' => isset($args['peer_id']) ? $args['peer_id'] : $this->bot->vkdata['peer_id'],
            'message' => $text
        ] + $args);
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
}
