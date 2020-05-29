<?php

class Message {

    public $bot, $keyboard = [], $buttons = [];
    const red = 'negative';
    const green = 'positive';
    const white = 'default';
    const blue = 'primary';

    /**
     * Message constructor.
     * @param Control $bot
     */
    public function __construct(Control $bot) {
        $this->bot = $bot;
    }

    /**
     * @param string $message
     * @param int $peer_id
     * @param int $from_id
     * @param array $params
     * @return array
     */
    public function sendMessage(string $message = "", int $peer_id = null, int $from_id = null, array $params = []) {
        if (!is_null($peer_id)) {
            $ms = [];
            $ms["random_id"] = time();
            $ms["peer_id"] = $peer_id;
            $ms["message"] = ($peer_id <= 2000000000) ? self::replaceNameToMessage($peer_id, $message) : self::replaceNameToMessage($from_id, $message);
            if (isset($params["attachment"])) $ms["attachment"] = $params["attachment"];
            if (isset($params["keyboard"])) $ms["keyboard"] = json_encode($params["keyboard"], JSON_UNESCAPED_UNICODE);
            if (isset($params["forward_messages"])) $ms["forward_messages"] = $params["forward_messages"];
            $this->bot->console->debug("Сообщение отправлено", $message);
            unset($this->keyboard, $this->buttons);
            return $this->bot->api("messages.send", $ms);
        }
    }

    /**
     * @param $uid
     * @param $message
     * @return string|string[]
     */
    private function replaceNameToMessage($uid, $message) {
        return str_replace(
            ["{fname}", "{lname}", "{afname}", "{alname}", "{fullname}", "{afullname}"],
            [
                self::getInfo($uid)['first_name'],
                self::getInfo($uid)['last_name'],
                "[id{$uid}|" . self::getInfo($uid)['first_name'] . "]",
                "[id{$uid}|" . self::getInfo($uid)['last_name'] . "]",
                self::getInfo($uid)['first_name'] . " " . self::getInfo($uid)['last_name'],
                "[id{$uid}|" . self::getInfo($uid)['first_name'] . " " . self::getInfo($uid)['last_name'] . "]",
            ], $message
        );
    }

    /**
     * @param int $user_id
     * @param string $name_case
     * @return mixed
     */
    public function getInfo(int $user_id, string $name_case = "") {
        return $this->bot->api("users.get", ["user_ids" => $user_id, "name_case" => $name_case])[0];
    }

    /**
     * @param array $keyboard
     * @param bool $one_time
     * @param bool $inline
     */
    public function addKeyboard(array $keyboard = [], bool $one_time = false, bool $inline = false) {
        foreach ($keyboard as $kfd => $kv) $this->buttons[] = $kv;
        $this->keyboard = ['one_time' => $one_time, 'inline' => $inline, 'buttons' => $this->buttons];
    }

    /**
     * @return array
     */
    public function remKeyboard() {
        return ['one_time' => true, 'buttons' => []];
    }

    /**
     * @param string $text
     * @param string $color
     * @param int $payload
     * @return array
     */
    public function addButton(string $text, $color = self::white, $payload = 1) {
        return [
            'action' => [
                'type' => 'text',
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'label' => $text],
            'color' => $color
        ];
    }

    /**
     * @param string $text
     * @param null $link
     * @param string $color
     * @param int $payload
     * @return array
     */
    public function addButtonLink(string $text, $link = null, $payload = 1) {
        return [
            'action' => [
                'type' => 'open_link',
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'link' => $link,
                'label' => $text]
        ];
    }

    /**
     * @return array
     */
    public function getKeyboard() {
        return $this->keyboard;
    }

}