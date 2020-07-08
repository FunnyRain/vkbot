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
     * @return array|null
     */
    public function sendComment(string $message = "", array $params = []) {
        if (empty($message))
            return null;
        
        $params = [
            'owner_id' => -$this->bot->group_id,
            'post_id' => $this->getPostId(),
            'from_group' => 1,
            'message' => self::replaceNameToMessage($this->getFromId(), $message),
            'reply_to_comment' => $this->getCommentId(),
        ];
        $this->bot->console->debug("post_id" . $this->getPostId() . "> Сообщение отправлено", $message);
        return $this->bot->api("wall.createComment", $params);
    }

    /**
     * @param string $type
     * @return mixed
     */
    private function getWallUploadServer(string $type = "photos") {
        if ($type === "docs") {
            return $this->bot->api("{$type}.getWallUploadServer", ["type" => "doc", "group_id" => $this->bot->group_id], true)["upload_url"];
        } else return $this->bot->api("{$type}.getWallUploadServer", ["group_id" => $this->bot->group_id], true)['upload_url'];
    }

    /**
     * @param string $title
     * @param string $hash
     * @param string $photo
     * @param int $server
     * @return string
     */
    private function saveWallPhoto(string $title, string $hash, string $photo, int $server) {
        $saveWallPhoto = $this->bot->api("photos.saveWallPhoto", [
            "group_id" => $this->bot->group_id,
            "hash" => $hash,
            "photo" => $photo,
            "server" => $server
        ], true);
        $this->bot->console->debug("сохранено");
        if (!isset($saveWallPhoto["error"])) {
            return "photo" . $saveWallPhoto[0]["owner_id"] . "_" . $saveWallPhoto[0]["id"];
        }
    }

    /**
     * @param string $src
     * @return string
     */
    public function uploadWallPhoto(string $src) {
        if (!file_exists($src)) {
            $this->bot->console->error("Загрузка фотографии невозможна!", "Неверный путь");
        } else {
            $server = $this->getWallUploadServer();
            $file = new CURLFile(realpath($src));
            $ch = curl_init($server);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => $file));
            $data = json_decode(curl_exec($ch), 1);
            curl_close($ch);
            $this->bot->console->debug("фотография отправлена");
            return $this->saveWallPhoto("test1", $data['hash'], $data['photo'], $data['server']);
        }
    }

    /**
     * @param string $message
     * @param array $params
     */
    public function addPost(string $message, array $params = []) {
        if (!isset($this->bot->page_token)) {
            $this->bot->console->error("Нельзя сделать пост без токена пользователя! Пример: ", "\$bot = new Control(\n\t\t\"токен\",\n\t\t\"айди группы (цифрами)\",\n\t\t5.102,\n\t\t\"токен пользователя\"\n\t);");
        } else {
            $params['message'] = empty($message) ? "hello world! XD" : $message;
            $params['owner_id'] = isset($params['owner_id']) ? $params['owner_id'] : -$this->bot->group_id;
            $params['from_group'] = isset($params['from_group']) ? $params['from_group'] : 1;
            if (isset($params["attachments"]) and is_array($params["attachments"])) {
                $params["attachments"] = implode(",", $params["attachments"]);
            } else {
                $params["attachments"] = isset($params["attachments"]) ? $params["attachments"] : null;
            }
            $this->bot->api('wall.post', $params, true);
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
