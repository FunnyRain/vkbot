<?php

class Message {

    public $bot;
    public $keyboard = [];
    public $buttons = [];
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
     * @param int|null $peer_id
     * @param int|null $from_id
     * @param array $params
     * @return array
     */
    public function sendMessage(string $message = "", int $peer_id = null, int $from_id = null, array $params = []) {
        if (!is_null($peer_id)) {
            $params["random_id"] = rand();
            $params["peer_id"] = $peer_id;
            $params["message"] = $peer_id <= 2000000000 ? self::replaceNameToMessage($peer_id, $message) : self::replaceNameToMessage($from_id, $message);
            if (isset($params["attachment"]) and is_array($params["attachment"])) {
                $params["attachment"] = implode(",", $params["attachment"]);
            } else {
                $params["attachment"] = isset($params["attachment"]) ? $params["attachment"] : null;
            }
            $params["forward_messages"] = isset($params["forward_messages"]) ? $params["forward_messages"] : null;
            if (isset($params["keyboard"])) $params["keyboard"] = json_encode($params["keyboard"], JSON_UNESCAPED_UNICODE);
            $this->bot->console->debug("{$peer_id}> Сообщение отправлено", !empty($message) ? $message : null);
            unset($this->keyboard, $this->buttons);
            return $this->bot->api("messages.send", $params);
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
        foreach ($keyboard as $kfd => $kv) {
            $this->buttons[] = $kv;
        }
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
     * @param string $payload
     * @return array
     */
    public function addButton(string $text, string $color = self::white, string $payload = "") {
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
     * @return array
     */
    public function addButtonLink(string $text, $link = null) {
        return [
            'action' => [
                'type' => 'open_link',
                'link' => $link,
                'label' => $text]
        ];
    }

    /**
     * Для этой функции пока нет описания. (Скоро)
     */
    public function addCallbackButton(string $text, string $payload = "") {
        if ($this->keyboard['inline'] == false) {
            $this->bot->console->error("Требуется параметр inline!");
        } else {
            return [
                'action' => [
                    'type' => 'callback',
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'label' => $text]
            ];
        }
    }

    /**
     * @return array
     */
    public function getKeyboard() {
        return $this->keyboard;
    }

    /**
     * @param string $type
     * @return mixed
     */
    private function getMessagesUploadServer(string $type = "photos") {
        if ($type === "docs") {
            global $peer_id;
            return $this->bot->api("{$type}.getMessagesUploadServer", ["type" => "doc", "peer_id" => $peer_id])["upload_url"];
        } else return $this->bot->api("{$type}.getMessagesUploadServer", [])["upload_url"];
    }

    /**
     * @param string $title
     * @param string $hash
     * @param string $photo
     * @param int $server
     * @return string
     */
    private function saveMessagesPhoto(string $title, string $hash, string $photo, int $server) {
        $saveMessagesPhoto = $this->bot->api("photos.saveMessagesPhoto", [
            "title" => $title,
            "hash" => $hash,
            "photo" => $photo,
            "server" => $server
        ]);
        if (!isset($saveMessagesPhoto["error"])) {
            return "photo" . $saveMessagesPhoto[0]["owner_id"] . "_" . $saveMessagesPhoto[0]["id"];
        }
    }

    /**
     * @param string $src
     * @return string
     */
    public function uploadPhoto(string $src) {
        if (!file_exists($src)) {
            $this->bot->console->error("Загрузка фотографии невозможна!", "Неверный путь");
        } else {
            $server = $this->getMessagesUploadServer();
            $file = new CURLFile(realpath($src));
            $ch = curl_init($server);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => $file));
            $data = json_decode(curl_exec($ch), 1);
            curl_close($ch);
            return $this->saveMessagesPhoto("test1", $data['hash'], $data['photo'], $data['server']);
        }
    }

    /**
     * @param string $title
     * @param string $doc
     * @return string
     */
    private function saveDoc(string $title, string $doc) {
        $saveDoc = $this->bot->api("docs.save", [
            "title" => $title,
            "file" => $doc,
        ]);
        if (!isset($saveDoc["error"])) {
            return "doc" . $saveDoc["doc"]["owner_id"] . "_" . $saveDoc["doc"]["id"];
        }
    }

    /**
     * @param string $src
     * @return string
     */
    public function uploadDoc(string $src) {
        if (!file_exists($src)) {
            $this->bot->console->error("Загрузка документа не возможна!", "Неверный путь");
        } else {
            $server = $this->getMessagesUploadServer("docs");
            $file = new CURLFile(realpath($src));
            $ch = curl_init($server);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => $file));
            $data = json_decode(curl_exec($ch), 1);
            curl_close($ch);
            return $this->saveDoc("test1", $data['file']);
        }
    }

}
