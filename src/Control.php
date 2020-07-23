<?php

/**
 * Control
 */
class Control {

    public $token;
    public $source_path = __DIR__;
    public $group_id;
    public $lp = [];
    public $console;
    public $message;
    public $wall;
    public $v;
    public $get;
    public $debug = 0;
    public $temp_datas_message = [];
    public $temp_datas_wall = [];
    //
    public $is_pagebot = false;
    //
    public $data;
    public $callback = false;
    //
    public $page_token;
    //
    public $callbackbutton = null;

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
        19 => "Контент недоступен.",
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
        911 => "Keyboard format is invalid",
        3300 => "Recaptcha needed.",
        3609 => "Token extensions required.",
        /** wall errors */
        212 => "Access to post comments denied",
        213 => "Нет доступа к комментированию записи",
        214 => "Access to adding post denied",
        219 => "Рекламный пост уже недавно публиковался.",
        220 => "Слишком много получателей.",
        222 => "Запрещено размещение ссылок в комментариях",
        223 => "Превышен лимит комментариев на стене",
        224 => "Too many ads posts",
        225 => "Donut is disabled",
        /** message errors */
        900 => "Нельзя отправлять сообщение пользователю из черного списка",
        901 => "Пользователь запретил отправку сообщений от имени сообщества",
        902 => "Нельзя отправлять сообщения этому пользователю в связи с настройками приватности",
        912 => "This is a chat bot feature, change this status in settings",
        913 => "Слишком много пересланных сообщений",
        914 => "Сообщение слишком длинное",
        917 => "You don't have access to this chat",
        921 => "Невозможно переслать выбранные сообщения",
        925 => "You are not admin of this chat",
        936 => "Contact not found",
        940 => "Too many posts in messages",
        943 => "Cannot use this intent",
        944 => "Limits overflow for this intent",
        945 => "Chat was disabled",
        946 => "Chat not supported",
        /** messages upload errors */
        114 => "Недопустимый идентификатор альбома.",
        118 => "Недопустимый сервер.",
        121 => "Неверный хэш."
    ];

    /**
     * Control constructor.
     * @param string $token
     * @param int $group_id
     * @param float $v
     * @param string|null $page_token
     */
    public function __construct(string $token, int $group_id, float $v = 5.102, string $page_token = null) {
        $this->token = $token;
        $this->v = $v;
        $this->page_token = $page_token;
        $this->console = new Console($this);
        $this->message = new Message($this);
        $this->wall = new Wall($this);
        if (empty($group_id)) {
            $this->is_pagebot = true;
            $this->console->warning("Используется страничная версия бота!", "Некоторые методы могут не работать.");
        } else {
            $this->group_id = $group_id;
        }
    }

    /** CallBack */
    public function setConfirm(string $code) {
        $this->callback = true;
        $data = json_decode(file_get_contents('php://input'));
        if (isset($data->type) && $data->type == 'confirmation') {
            exit($code);
        } else $this->data = json_decode(file_get_contents('php://input'));
    }

    public function ok() {
        set_time_limit(0);
        ini_set('display_errors', 0);
        ob_end_clean();

        // для Nginx
        if (is_callable('fastcgi_finish_request')) {
            echo 'ok';
            session_write_close();
            fastcgi_finish_request();
            return true;
        }
        // для Apache
        ignore_user_abort(true);

        ob_start();
        header('Content-Encoding: none');
        header('Content-Length: 2');
        header('Connection: close');
        echo 'ok';
        ob_end_flush();
        flush();
        return true;
    }

    /**
     * @return array|null
     */
    public function getAction() {
        return isset($this->get["action"]) ? (array)$this->get["action"] : null;
    }

    /**
     * @return string|null
     */
    public function getMessage() {
        if (isset($this->get["text"])) {
            if (mb_strpos($this->get["text"], '] '))
                return (string)explode('] ', $this->get["text"])[1];

            return (string)$this->get["text"];
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getFromId() {
        return isset($this->get["from_id"]) ? (int)$this->get["from_id"] : null;
    }

    /**
     * @return int|null
     */
    public function getPeerId() {
        return isset($this->get["peer_id"]) ? (int)$this->get["peer_id"] : null;
    }

    /**
     * @return int|null
     */
    public function getMessageId() {
        return isset($this->get["conversation_message_id"]) ? (int)$this->get["conversation_message_id"] : null;
    }

    /**
     * @return array|null
     */
    public function getAttachment() {
        return isset($this->get["attachments"]) ? (array)$this->get["attachments"] : null;
    }

    /**
     * @return string|null
     */
    public function getPayload() {
        return isset($this->get["payload"]) ? (string)str_replace(['"', "'"], '', $this->get["payload"]) : null;
    }

    /**
     * Для этой функции пока нет описания. (Скоро)
     */
    public function getCallbackButton() {
        return $this->callbackbutton;
    }

    /**
     * Debug mode
     * @param int $ok
     */
    public function debug($ok = 1) {
        ini_set('display_errors', $ok);
        date_default_timezone_set('Europe/Moscow');
        $this->debug = $ok;
    }

    public function start() {
        if ($this->callback === true) {
            /* без комментариев.... */
            $get = [];
            foreach ($this->data->object as $key => $value) {
                $get[$key] = $value;
            }
            $this->get = $get;
            $this->ok();
        } else {
            $getArray = $this->getRequest();
            if (isset($getArray["updates"])) {
                foreach ($getArray["updates"] as $get) {
                    if (!isset($get['type'])) $get['type'] = 'aaaaaaaaaa';
                    switch ($get["type"]) {
                        case "message_event":
                            $this->callbackbutton = $get['object'];
                            break;
                        case "message_new":
                            if (!isset($this->temp_datas_message[$get["object"]["peer_id"]])) $this->temp_datas_message[$get["object"]["peer_id"]][] = -1;
                            if (!in_array($get["object"]["conversation_message_id"], $this->temp_datas_message[$get["object"]["peer_id"]])) {
                                $this->temp_datas_message[$get["object"]["peer_id"]][] = $get["object"]["conversation_message_id"];
                                $this->get = $get["object"];
                            } else {
                                $this->get = null;
                            }
                            break;
                        case "wall_reply_new":
                            if (!isset($this->temp_datas_wall[$get["object"]["post_id"]])) $this->temp_datas_wall[$get["object"]["post_id"]][] = -1;
                            if (!in_array($get["object"]["id"], $this->temp_datas_wall[$get["object"]["post_id"]])) {
                                $this->temp_datas_wall[$get["object"]["post_id"]][] = $get["object"]["id"];
                                $this->wall->object($get["object"]);
                            } else {
                                $this->wall->object([]);
                            }
                            break;
                            default:
                            if ($this->is_pagebot) {
                                $obj1 = [];
                                /** Var_Dump:
                                 * Array (
                                        [0] => 4 // ? хз что это
                                        [1] => 4958522 // ? айди сообщения
                                        [2] => 8243 // ? тоже хз
                                        [3] => 2000000391 // ? чат
                                        [4] => 1595506089 // ? как я понял , это время сообщения (unix)
                                        [5] => test message // ? текст
                                        [6] => Array ( 
                                                [from] => 439239695 // ? отправитель
                                            )
                                        [7] => Array (
                                                [attach1_type] => photo // ? тип документа
                                                [attach1] => 439239695_457251508 // ? документ
                                            )
                                        [type] => aaaaaaaaaa // ! это не нужно
                                    )
                                 */
                                $conversation_message_id = !empty($get[1]) ? $get[1] : null;
                                $peer_id = !empty($get[3]) ? $get[3] : null;
                                if ($peer_id < 2000000000) {
                                    $from_id = $peer_id;
                                } else {
                                    $from_id = !empty($get[6]) ? $get[6]['from'] : null;
                                }
                                $text = !empty($get[5]) ? $get[5] : null;
                                $attachments = !empty($get[7]) ? $get[7] : null;
                                if (!empty($get[6])) print_r($get);
                                $obj1['object'] = [
                                    'conversation_message_id' => $conversation_message_id,
                                    'peer_id' => $peer_id,
                                    'from_id' => $from_id,
                                    'text' => $text,
                                    'attachments' => $attachments
                                ];
                                $this->get = $obj1['object'];
                            } else {
                                // TODO: Неизвестный тип
                            }
                            break;
                    }
                }
            }
        }
    }

    /** utils */
    public function setOnline() {
        if (self::api("groups.getOnlineStatus", ["group_id" => $this->group_id])['status'] == 'none') {
            return self::api("groups.enableOnline", ["group_id" => $this->group_id]);
        }
    }

    public function getShortLink(string $url = "https://google.com", int $private = 0) {
        if (isset($url)) {
            $getShortLink = self::api("utils.getShortLink", ["url" => $url, "private" => $private]);
            if (isset($getShortLink['short_url']))
                return $getShortLink['short_url'];

            $this->console->warning("Не удалось сократить ссылку", $url);
            return "";
        }
    }

    /**
     * @param string $url
     * @return mixed
     */
    public function call(string $url) {
        if (function_exists("curl_init"))
            $sendRequest = $this->curl_post($url);
        else
            $sendRequest = file_get_contents($url);

        $sendRequest = json_decode($sendRequest, true);
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
        if ($this->is_pagebot) {
            return $this->api("messages.getLongPollServer", ["need_pts" => 1]);
        } else {
            return $this->api("groups.getLongPollServer", ["group_id" => $this->group_id]);
        }
    }

    /**
     * @return mixed
     */
    public function getRequest() {
        $url = json_decode(@file_get_contents($this->lp["url"]), 1);
        if (isset($url["updates"]))
            return json_decode(file_get_contents($this->lp["url"]), 1);

        $result = $this->getLongPollServer();
        $ts = $result["ts"];
        $key = $result["key"];
        $server = $result["server"];
        if ($this->is_pagebot) {
            $this->lp["url"] = "https://{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=2&version=3";
        } else {
            $this->lp["url"] = "{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=8&version=3";
        }
        $this->console->log("Ссылка лонгпулла обновлена");

        return json_decode(file_get_contents($this->lp["url"]), 1);
    }

    /**
     * @param string $method
     * @param array $params
     * @param bool $is_page
     * @return array
     */
    public function api(string $method = '', array $params = [], bool $is_page = false) {
        $params["v"] = $this->v;
        $params["access_token"] = $is_page === true ? $this->page_token : $this->token;
        $params = http_build_query($params);
        $url = $this->http_build_query($method, $params);

        return (array)$this->call($url);
    }

    /**
     * @param string $method
     * @param string $params
     * @return string
     */
    private function http_build_query(string $method, string $params = '') {
        return "https://api.vk.com/method/{$method}?{$params}";
    }

    /**
     * @param string $url
     * @return bool|string
     */
    private function curl_post(string $url) {
        if (!function_exists("curl_init")) return false;
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
