<?php

class Bot {

    /** Токен */
    public $token;
    /** Версия Апи */
    public $v = 5.102;
    /** Айди группы */
    public $group_id;
    /** Путь до папки */
    public $source_path = __DIR__;

    /** Временные данные ЛонгПулла */
    public $key;
    public $ts;
    public $server;
    /** Временные данные ЛонгПулла (Используется для reply('text') и тд) */
    public $vkdata;
    /** @var Logger */
    private $logger;
    /** @var KeyboardBuilder */
    private $builder;

    /** Для групповой беседы  */
    const PEER_ID = 2000000000;

    /**
     * new Bot("токен", "версия ВкАпи", "айди группы")
     * @param [type] ...$args
     */
    public function __construct(...$args) {
        $this->logger = new Logger();
        $this->builder = new KeyboardBuilder();
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
    public function setToken(string $token): void {
        $this->token = $token;
    }

    /**
     * Устанавливает версию ВкАпи
     * @param float $v
     * @return void
     */
    public function setVersion(float $v): void {
        $this->v = $v;
    }

    /**
     * Устанавливает айди группы
     * @param int $group_id
     * @return void
     */
    public function setGroupId(int $group_id): void {
        $this->group_id = $group_id;
    }

    /**
     * Класс логирования
     * @return Logger
     */
    public function getLog(): Logger {
        return $this->logger;
    }

    /**
     * Класс сообщения
     * @return Messages
     */
    public function getMessage(): Messages {
        return new Messages($this);
    }

    /**
     * Класс сборщика клавиатуры
     * @return KeyboardBuilder
     */
    public function kBuilder(): KeyboardBuilder {
        return $this->builder;
    }

    /**
     * Проверка токена на валид
     * @return void
     */
    public function isValidateToken(): void {
        $test = $this->api('groups.getById');
        if (isset($test[0]['id']) and ($test[0]['type'] == 'group' or $test[0]['type'] == 'page')) {
            $this->group_id = $test[0]['id'];
            $this->getLog()->log('Токен рабочий! group_id: ' . $this->group_id);
            $this->api('groups.setLongPollSettings', [
                'group_id' => $this->group_id,
                'enabled' => 1,
                'api_version' => $this->v,
                'message_new' => 1,
            ]);
            $this->getLog()->log('Настройки Longpoll в группе выставлены автоматически! Ничего менять не нужно');
        } else die($this->getLog()->error('Токен не рабочий!'));
    }

    /**
     * Старт бота
     * @param [type] $listen
     * @return void
     */
    public function start($listen) {
        if (empty($this->token)) die($this->getLog()->error('Не указан токен!'));
        $this->isValidateToken();
        $this->getLongPollServer();

        while ($data = $this->getRequest()) {
            if (!isset($data["ts"])) {
                var_dump($data);
                $this->getLog()->log("TIMESTAMP не получен...\n");
                continue;
            }

            //! Тестируется, возможно, не самое лучшее решение.
            $updates = $data['updates'];
            if (count($updates) == 0) continue;

            foreach ($updates as $key => $update) {
                $object = $updates[$key]['object'];
                $this->vkdata = (isset($object['message'])) ? $object['message'] + $object['client_info'] + ['type' => $updates[$key]['type']]
                    : $object + ['type' => $updates[$key]['type']];
                $listen($object);
            }
        }
    }

    public function isAction(string $type = 'message_new', $listen) {
        $object = $this->vkdata;
        if (isset($object['action'])) {
            if ($object['action']['type'] == $type) {
                $listen($object['action']);
            }
        }
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

    /**
     * Получение сервера ЛонгПулла
     */
    public function getLongPollServer() {
        $data = $this->api("groups.getLongPollServer", ["group_id" => $this->group_id]);
        $this->getLog()->log("Ссылка лонгпулла обновлена\n");
        list($this->key, $this->server, $this->ts) = [$data['key'], $data['server'], $data['ts']];
    }

    /**
     * Получение всех событий
     * @return array
     */
    public function getRequest(): array {
        $result = $this->getData();
        if (isset($result["failed"])) {
            if ($result["failed"] == 1) {
                unset($this->ts);
                $this->ts = $result["ts"];
            } else {
                $this->getLongPollServer();
                $result = $this->getData();
            }
        }

        $this->ts = $result["ts"];
        return $result;
    }

    /**
     * @return mixed
     */
    public function getData() {
        $defult_params = ['act' => 'a_check', 'key' => $this->key, 'ts' => $this->ts, 'wait' => 25];
        $data = json_decode($this->curlRequest($this->server . '?' . http_build_query($defult_params)), 1);
        return $data;
    }

    /**
     * Выполнение Апи запросов, возвращает Массив.
     * Список методов: https://vk.com/dev/methods
     * @param string $method
     * @param array $params
     * @return array
     */
    public function api(string $method = '', array $params = []): array {
        $params["v"] = $this->v;
        $params["access_token"] = $this->token;
        $params = http_build_query($params);
        $url = $this->http_build_query($method, $params);

        return (array)$this->call($url);
    }

    private function http_build_query(string $method, string $params = ''): string {
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

    private function curlRequest($url) {
        $myCurl = curl_init();
        curl_setopt_array($myCurl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $response = curl_exec($myCurl);

        return $response;
    }
}
