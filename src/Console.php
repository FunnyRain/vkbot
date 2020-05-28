<?php

class Console {

    private Control $bot;
    private array $foreground_colors = [];
    private array $background_colors = [];

    public function __construct(Control $bot) {
        $this->bot = $bot;
        $this->foreground_colors = [
            "black" => "0;30",
            "dark_gray" => "1;30",
            "light_gray" => "0;37",
            "blue" => "0;34",
            "light_blue" => "1;34",
            "green" => "0;32",
            "light_green" => "1;32",
            "cyan" => "0;36",
            "light_cyan" => "1;36",
            "red" => "0;31",
            "light_red" => "1;31",
            "purple" => "0;35",
            "light_purple" => "1;35",
            "brown" => "0;33",
            "yellow" => "1;33",
            "white" => "1;37"
        ];
        $this->background_colors = [
            "black" => "40",
            "red" => "41",
            "green" => "42",
            "yellow" => "43",
            "blue" => "44",
            "magenta" => "45",
            "cyan" => "46",
            "light_gray" => "47",
        ];
    }

    public function log($title = null, $subtitle = null) {
        echo PHP_EOL . self::color("[Лог] > " . date("d.m.y, H:i:s") . "> ", "blue") . self::color($title, "white");
        if (!is_null($subtitle)) echo PHP_EOL . "\t" . self::color($subtitle, "green");
    }

    public function error($title = null, $subtitle = null) {
        echo PHP_EOL . self::color("[Ошибка] > " . date("d.m.y, H:i:s") . "> ", "red") . self::color($title, "white");
        if (!is_null($subtitle)) echo PHP_EOL . "\t" . self::color($subtitle, "green");
    }

    public function warning($title = null, $subtitle = null) {
        echo PHP_EOL . self::color("[Предупреждение] > " . date("d.m.y, H:i:s") . "> ", "yellow") . self::color($title, "white");
        if (!is_null($subtitle)) echo PHP_EOL . "\t" . self::color($subtitle, "green");
    }

    public function debug($title = null, $subtitle = null) {
        if ($this->bot->debug == 1) {
            echo PHP_EOL . self::color("[Дебаг] > " . date("d.m.y, H:i:s") . "> ", "purple") . self::color($title, "white");
            if (!is_null($subtitle)) echo PHP_EOL . "\t" . self::color($subtitle, "light_purple");
        }
    }

    public function color($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }
        $colored_string .= $string . "\033[0m";
        return $colored_string;
    }
}