<?php

class Control {

    public $token, $source_path = __DIR__, $group_id, $lp = [],
    $console, $message;

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
            $code_error = $sendRequest["error"];
            switch ($code_error["error_code"]) {
                case 1:
                    $this->console->error("Произошла неизвестная ошибка.", "Попробуйте повторить запрос позже.");
                    break;
                case 2:
                    $this->console->error("Приложение выключено.", "Необходимо включить приложение в настройках https://vk.com/editapp?id={Ваш API_ID} или использовать тестовый режим (test_mode=1)");
                    break;
                case 3:
                    $this->console->error("Передан неизвестный метод.", "Проверьте, правильно ли указано название вызываемого метода: https://vk.com/dev/methods.");
                    break;
                case 4:
                    $this->console->error("Неверная подпись.");
                    break;
                case 5:
                    $this->console->error("Авторизация пользователя не удалась.", "Убедитесь, что Вы используете верную схему авторизации.");
                    break;
                case 6:
                    $this->console->error("Слишком много запросов в секунду.", "Задайте больший интервал между вызовами или используйте метод execute. Подробнее об ограничениях на частоту вызовов см. на странице https://vk.com/dev/api_requests.");
                    break;
                case 7:
                    $this->console->error("Нет прав для выполнения этого действия.", "Проверьте, получены ли нужные права доступа при авторизации. Это можно сделать с помощью метода account.getAppPermissions.");
                    break;
                case 8:
                    $this->console->error("Неверный запрос.", "Проверьте синтаксис запроса и список используемых параметров (его можно найти на странице с описанием метода).");
                    break;
                case 9:
                    $this->console->error("Слишком много однотипных действий.", "Нужно сократить число однотипных обращений. Для более эффективной работы Вы можете использовать execute или JSONP.");
                    break;
                case 10:
                    $this->console->error("Произошла внутренняя ошибка сервера.", "Попробуйте повторить запрос позже.");
                    break;
                case 14:
                    $this->console->error("Требуется ввод кода с картинки (Captcha).", "Процесс обработки этой ошибки подробно описан на отдельной странице. (https://vk.com/dev/captcha_error)");
                    break;
                case 15:
                    $this->console->error("Доступ запрещён.", "Убедитесь, что Вы используете верные идентификаторы, и доступ к контенту для текущего пользователя есть в полной версии сайта.");
                    break;
                case 18:
                    $this->console->error("Страница удалена или заблокирована.", "Страница пользователя была удалена или заблокирована");
                    break;
                case 21:
                    $this->console->error("Данное действие разрешено только для Standalone и Open API приложений");
                    break;
                case 23:
                    $this->console->error("Метод был выключен.", "Все актуальные методы ВК API, которые доступны в настоящий момент, перечислены здесь: https://vk.com/dev/methods.");
                    break;
                case 27:
                    $this->console->error("Ключ доступа сообщества недействителен.");
                    break;
                case 28:
                    $this->console->error("Ключ доступа приложения недействителен.");
                    break;
                case 29:
                    $this->console->error("Достигнут количественный лимит на вызов метода", "Подробнее об ограничениях на количество вызовов см. на странице https://vk.com/dev/data_limits");
                    break;
                case 30:
                    $this->console->error("Профиль является приватным", "Информация, запрашиваемая о профиле, недоступна с используемым ключом доступа");
                    break;
                case 33:
                    $this->console->error("Not implemented yet");
                    break;
                case 100:
                    $this->console->error("Один из необходимых параметров был не передан или неверен.", "Проверьте список требуемых параметров и их формат на странице с описанием метода.");
                    break;
                case 113:
                    $this->console->error("Неверный идентификатор пользователя.", "Убедитесь, что Вы используете верный идентификатор. Получить ID по короткому имени можно методом utils.resolveScreenName.");
                    break;
                case 150:
                    $this->console->error("Неверный timestamp.", "Получить актуальное значение Вы можете методом utils.getServerTime.");
                    break;
                case 200:
                    $this->console->error("Доступ к альбому запрещён.");
                    break;
                case 201:
                    $this->console->error("Доступ к аудио запрещён.");
                    break;
                case 203:
                    $this->console->error("Доступ к группе запрещён.");
                    break;
                case 300:
                    $this->console->error("Альбом переполнен.");
                    break;
                case 3300:
                    $this->console->error("Recaptcha needed");
                    break;
                case 3609:
                    $this->console->error("Token extension required");
                    break;
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