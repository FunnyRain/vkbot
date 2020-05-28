<?php

class Control {

    public $token, $source_path = __DIR__, $group_id, $lp = [],
        $console, $message;

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
        3609 => "Token extensions required."
    ];

    public function __construct(string $token, int $group_id, float $v = 5.102) {
        $this->token = $token;
        $this->group_id = $group_id;
        $this->v = $v;
        $this->console = new Console();
        $this->message = new Message($this);
    }

    public function start() {
        //todo
    }

    public function call(string $url) {
        if (function_exists("curl_init")) {
            $sendRequest = $this->curl_post($url);
        } else {
            $sendRequest = file_get_contents($url);
        }
        $sendRequest = json_decode($sendRequest, 1);
        if (isset($sendRequest["error"])) {
            $error = $sendRequest["error"]["error_code"];
            if(isset(self::ERRORS[$error])){
                $this->console->error(self::ERRORS[$error]);
            }else{
                $this->console->error("Произошла неизвестная ошибка.");
            }
        }
        if (isset($sendRequest["response"])) {
            return $sendRequest["response"];
        }
        return $sendRequest;
    }

    public function getLongPollServer() {
        $ms = [];
        $ms["group_id"] = $this->group_id;
        return $this->api("groups.getLongPollServer", $ms);
    }
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

    public function api(string $method = '', array $params = []) {
        $params["v"] = $this->v;
        $params["access_token"] = $this->token;
        $params = http_build_query($params);
        $url = $this->http_build_query($method, $params);
        return (array)$this->call($url);
    }
    private function http_build_query($method, $params = '') {
        return "https://api.vk.com/method/{$method}?{$params}";
    }

    /**
     * Временное решение
     */
    public function curl_post(string $urlq) {
        $urls = [$urlq];
        $multi = curl_multi_init();
        $channels = array();
        $active = null;
        foreach ($urls as $url) {
            $ch = curl_init();
            $param = parse_url($url);
            curl_setopt($ch, CURLOPT_URL, $param["scheme"] . '://' . $param["host"] . $param["path"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param["query"]);
            curl_multi_add_handle($multi, $ch);
            $channels[$url] = $ch;
        }
        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi) == -1) {
                continue;
            }
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        foreach ($channels as $channel) {
            return curl_multi_getcontent($channel);
            curl_multi_remove_handle($multi, $channel);
        }
        curl_multi_close($multi);
    }
}