<?php

class Messages {

    public Bot $bot;

    public function __construct(Bot $bot) {
        $this->bot = $bot;
    }

    public function get() {
        print_r($this->bot->vkdata);
    }
}
