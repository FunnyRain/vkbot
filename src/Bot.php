<?php 

class Bot {

    public string $token;
    public float $v = 5.102;
    public int $group_id;
    public string $source_path = __DIR__;

    public array $lp = [];

    /**
     * new Bot("токен", "версия ВкАпи", "айди группы")
     * @param [type] ...$args
     */
    public function __construct(...$args) {
        if (!empty($args)) {
            foreach ($args as $type) {
                if (is_string($type)) $this->token = $type;
                if (is_float($type)) $this->v = $type;
                if (is_integer($type)) $this->group_id = $type;
            }
        }
    }

    /**
     * Устанавливает токен
     * @param string $token
     * @return void
     */
    public function setToken(string $token): void{
        $this->token = $token;
    }

    /**
     * Устанавливает версию ВкАпи
     * @param float $v
     * @return void
     */
    public function setVersion(float $v): void{
        $this->v = $v;
    }

    /**
     * Устанавливает айди группы
     * @param int $group_id
     * @return void
     */
    public function setGroupId(int $group_id): void{
        $this->group_id = $group_id;
    }

    /**
     * Класс логирования
     * @return void
     */
    public function getLog(): object{
        return new Logger();
    }

    public function isValidateToken(): void{
        $test = $this->api('groups.getById');
        if (isset($test[0]['id']) and $test[0]['type'] == 'group') {
            $this->group_id = $test[0]['id'];
            $this->getLog()->log('Токен рабочий! group_id: ' . $this->group_id);
        } else die($this->getLog()->error('Токен не рабочий!'));
    }

    public function start() {
        if (empty($this->token)) die($this->getLog()->error('Не указан токен!'));
        $this->isValidateToken();
        // while (true) {
        //     new Messages($this, $this->getRequest());
        // }
    }

    public function call(string $url) {
        if (function_exists("curl_init"))
            $sendRequest = $this->curl_post($url);
        else
            $sendRequest = file_get_contents($url);

        $sendRequest = json_decode($sendRequest, true);
        if (isset($sendRequest["error"])) {
            $error = $sendRequest["error"]["error_code"];
            if (isset(ERRORS[$error])) {
                $method = isset($sendRequest["error"]["request_params"][0]["value"]) ? $sendRequest["error"]["request_params"][0]["value"] : null;
                $this->getLog()->error(ERRORS[$error], "Метод: {$method}");
            } else {
                $this->getLog()->error("Произошла неизвестная ошибка.");
            }
        }

        if (isset($sendRequest["response"])) {
            return $sendRequest["response"];
        }

        return $sendRequest;
    }

    public function getLongPollServer() {
        return $this->api("groups.getLongPollServer", ["group_id" => $this->group_id]);
    }

    public function getRequest() {
        $url = json_decode(@file_get_contents($this->lp["url"]), 1);
        if (isset($url["updates"]))
            return json_decode(file_get_contents($this->lp["url"]), 1);

        $result = $this->getLongPollServer();
        $ts = $result["ts"];
        $key = $result["key"];
        $server = $result["server"];
        $this->lp["url"] = "{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=8&version=3";
        $this->getLog()->log("Ссылка лонгпулла обновлена");

        return json_decode(file_get_contents($this->lp["url"]), 1);
    }

    public function api(string $method = '', array $params = []) {
        $params["v"] = $this->v;
        $params["access_token"] = $this->token;
        $params = http_build_query($params);
        $url = $this->http_build_query($method, $params);

        return (array)$this->call($url);
    }

    private function http_build_query(string $method, string $params = '') {
        return "https://api.vk.com/method/{$method}?{$params}";
    }

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
