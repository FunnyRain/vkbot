<?php

class Message {

    public Control $bot;

    public function __construct(Control $bot) {
        $this->bot = $bot;
    }

    public function sendMessage(string $message, int $peer_id, array $params = []) {
        $ms = [];
        $ms["random_id"] = time();
        $ms["peer_id"] = $peer_id;
        $ms["message"] = ($peer_id <= 2000000000) ? self::replaceNameToMessage($peer_id, $message) : self::replaceNameToMessage($params["uid"], $message);
        if (isset($params["attachment"])) $ms["attachment"] = $params["attachment"];
        if (isset($params["keyboard"])) $ms["keyboard"] = $params["keyboard"];
        if (isset($params["forward_messages"])) $ms["forward_messages"] = $params["forward_messages"];
        $this->bot->console->debug("Сообщение отправлено", $message);
        return $this->bot->api("messages.send", $ms);
    }

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

    public function getInfo(int $user_id, string $name_case = "") {
        return $this->bot->api("users.get", ["user_ids" => $user_id, "name_case" => $name_case])[0];
    }

}