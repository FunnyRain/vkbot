<?php

class Wall {

    public $bot;
    public $object;

    /**
     * Wall constructor.
     * @param Control $bot
     */
    public function __construct(Control $bot) {
        $this->bot = $bot;
    }

    /**
     * @param string $message
     * @param array $params
     * @return array
     */
    public function sendComment(string $message = "", array $params = []) {
        if (!empty($message)) {
            $params["owner_id"] = -$this->bot->group_id;
            $params["post_id"] = $this->getPostId();
            $params["from_group"] = 1;
            $params["message"] = isset($message) ? self::replaceNameToMessage($this->getFromId(), $message) : null;
            $params["reply_to_comment"] = $this->getCommentId();
            $this->bot->console->debug("post_id{$this->getPostId()}> Сообщение отправлено", !empty($message) ? $message : null);
            return $this->bot->api("wall.createComment", $params);
        }
    }

    /**
     * @param int $uid
     * @param string $message
     * @return string|string[]
     */
    public function replaceNameToMessage(int $uid, string $message) {
        return str_replace(
            ["{fname}", "{lname}", "{afname}", "{alname}", "{fullname}", "{afullname}"],
            [
                $this->bot->message->getInfo($uid)['first_name'],
                $this->bot->message->getInfo($uid)['last_name'],
                "[id{$uid}|" . $this->bot->message->getInfo($uid)['first_name'] . "]",
                "[id{$uid}|" . $this->bot->message->getInfo($uid)['last_name'] . "]",
                $this->bot->message->getInfo($uid)['first_name'] . " " . $this->bot->message->getInfo($uid)['last_name'],
                "[id{$uid}|" . $this->bot->message->getInfo($uid)['first_name'] . " " . $this->bot->message->getInfo($uid)['last_name'] . "]",
            ], $message
        );
    }

    /**
     * @return string|null
     */
    public function getMessage() {
        return isset($this->object["text"]) ? (string)$this->object["text"] : null;
    }

    /**
     * @return int|null
     */
    public function getFromId() {
        return isset($this->object["from_id"]) ? (int)$this->object["from_id"] : null;
    }

    /**
     * @return int|null
     */
    public function getPostId() {
        return isset($this->object["post_id"]) ? (int)$this->object["post_id"] : null;
    }

    /**
     * @return int|null
     */
    public function getCommentId() {
        return isset($this->object["id"]) ? (int)$this->object["id"] : null;
    }

    /**
     * @param array $object
     */
    public function object(array $object) {
        $this->object = $object;
    }
}
