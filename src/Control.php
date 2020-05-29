<?php

class Control {

    public $token, $source_path = __DIR__, $group_id, $lp = [], $console, $message, $v,
        $get, $debug = 0;

    const ERRORS = [
        1 => "Произошла неизвестная ошибка.",
        2 => "Приложение выключено.",
        3 => "Передан неизвестный метод.",
        4 => "Неверная подпись.",
        5 => "Авторизация пользователя не удалась.",
        6 => "Слишком много запросов в секунду.",
        7 => "Нет прав для выполнения этого действия.",
        8 => "Неверный запрос.",
        9 => "Слишком много однотипных действий.",
        10 => "Произошла внутренняя ошибка сервера.",
        14 => "Требуется ввод кода с картинки (Captcha).",
        15 => "Доступ запрещён.",
        18 => "Страница удалена или заблокирована.",
        21 => "Данное действие разрешено только для Standalone и Open API приложений.",
        23 => "Метод был выключен.",
        27 => "Ключ доступа сообщества недействителен.",
        28 => "Ключ доступа приложения недействителен.",
        29 => "Достигнут количественный лимит на вызов метода.",
        30 => "Профиль является приватным.",
        33 => "Not implemented yet.",
        100 => "Один из необходимых параметров был не передан или неверно.",
        113 => "Неверный идентификатор пользователя.",
        150 => "Неверный timestamp.",
        200 => "Доступ к альбому запрещён.",
        201 => "Доступ к аудио запрещён.",
        203 => "Доступ к сообществу запрещён.",
        300 => "Альбом переполнен.",
        3300 => "Recaptcha needed.",
        3609 => "Token extensions required.",
        911 => "Keyboard format is invalid"
    ];

    /**
     * Control constructor.
     * @param string $token
     * @param int $group_id
     * @param float $v
     */
    public function __construct(string $token, int $group_id, float $v = 5.102) {
        $this->token = $token;
        $this->group_id = $group_id;
        $this->v = $v;
        $this->console = new Console($this);
        $this->message = new Message($this);
    }

    /**
     * @return string|null
     */
    public function getMessage() {
        return isset($this->get["text"]) ? (string) $this->get["text"] : null;
    }

    /**
     * @return int|null
     */
    public function getFromId() {
        return isset($this->get["from_id"]) ? (int) $this->get["from_id"] : null;
    }

    /**
     * @return int|null
     */
    public function getPeerId() {
        return isset($this->get["peer_id"]) ? (int) $this->get["peer_id"] : null;
    }

    /**
     * @return int|null
     */
    public function getMessageId() {
        return isset($this->get["conversation_message_id"]) ? (int) $this->get["conversation_message_id"] : null;
    }

    /**
     * @return array|null
     */
    public function getAttachment() {
        return isset($this->get["attachments"]) ? (array) $this->get["attachments"] : null;
    }

    /**
     * @return string|null
     */
    public function getPayload() {
        return isset($this->get["payload"]) ? (string) str_replace(['"',"'"], '', $this->get["payload"]) : null;
    }

    /**
     * Debug mode
     */
    public function debug() {
        $this->debug = 1;
    }

    public function start() {
        $getArray = $this->getRequest();
        if (isset($getArray["updates"])) {
            foreach ($getArray["updates"] as $get) {
                switch ($get["type"]) {
                    case "message_new":
                        $this->get = $get["object"];
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * @param string $url
     * @return mixed
     */
    public function call(string $url) {
        if (function_exists("curl_init")) $sendRequest = $this->curl_post($url); else $sendRequest = file_get_contents($url);
        $sendRequest = json_decode($sendRequest, 1);
        if (isset($sendRequest["error"])) {
            $error = $sendRequest["error"]["error_code"];
            if (isset(self::ERRORS[$error])) {
                $method = isset($sendRequest["error"]["request_params"][0]["value"]) ? $sendRequest["error"]["request_params"][0]["value"] : null;
                $this->console->error(self::ERRORS[$error], "Метод: {$method}");
            } else {
                $this->console->error("Произошла неизвестная ошибка.");
            }
        }
        if (isset($sendRequest["response"])) {
            return $sendRequest["response"];
        }
        return $sendRequest;
    }

    /**
     * @return array
     */
    public function getLongPollServer() {
        $ms = [];
        $ms["group_id"] = $this->group_id;
        return $this->api("groups.getLongPollServer", $ms);
    }

    /**
     * @return mixed
     */
    public function getRequest() {
        $url = json_decode(@file_get_contents($this->lp["url"]), 1);
        if (isset($url["updates"])) {
            return json_decode(file_get_contents($this->lp["url"]), 1);
        } else {
            $result = $this->getLongPollServer();
            $ts = $result["ts"];
            $key = $result["key"];
            $server = $result["server"];
            $this->lp["url"] = "{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=8&version=3";
            $this->console->log("Ссылка лонгпулла обновлена");
            return json_decode(file_get_contents($this->lp["url"]), 1);
        }
    }

    /**
     * @param string $method
     * @param array $params
     * @return array
     */
    public function api(string $method = '', array $params = []) {
        $params["v"] = $this->v;
        $params["access_token"] = $this->token;
        $params = http_build_query($params);
        $url = $this->http_build_query($method, $params);
        return (array)$this->call($url);
    }

    /**
     * @param $method
     * @param string $params
     * @return string
     */
    private function http_build_query($method, $params = '') {
        return "https://api.vk.com/method/{$method}?{$params}";
    }

    /**
     * @param $url
     * @return bool|string
     */
    private function curl_post($url) {
        if (!function_exists('curl_init')) return false;
        $param = parse_url($url);
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $param["scheme"] . "://" . $param["host"] . $param["path"]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param["query"]);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            $out = curl_exec($curl);
            curl_close($curl);
            return $out;
        }
        return false;
    }
}
