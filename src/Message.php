<?php

class Message {

    public $bot;

    public function __construct(Control $bot) {
        $this->bot = $bot;
    }

    public function sendMessage(string $message, int $peer_id, array $params = []) {
        $ms = [];
        $ms["random_id"] = time();
        $ms["peer_id"] = $peer_id;
        $ms["message"] = $message;
        if (isset($params["attachment"])) $ms["attachment"] = $params["attachment"];
        if (isset($params["keyboard"])) $ms["keyboard"] = $params["keyboard"];
        if (isset($params["forward_messages"])) $ms["forward_messages"] = $params["forward_messages"];
        return $this->bot->api("messages.send", $ms);
    }

}