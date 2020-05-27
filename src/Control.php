<?php

class Control {

    public $token, $source_path = __DIR__, $group_id;

    public function __construct(String $token, Int $group_id, float $v = 5.102) {
        $this->token = $token;
        $this->group_id = $group_id;
        $this->v = $v;
    }

    public function start() {
        //todo
    }
    public function call($url) {
        if (function_exists("curl_init")) {
            // $sendRequest = $this->curl_post($url);
        } else {
            $sendRequest = file_get_contents($url);
        }
        $sendRequest = json_decode($sendRequest, 1);
        if ($code_error = isset($sendRequest["error"])) {
            switch ($code_error["error_code"]) {
                case 1:

                    break;
            }
        }
    }

}