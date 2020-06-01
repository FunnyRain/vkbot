<?php

class Console {

    public $bot;
    
    public $foreground_colors = [
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
    
    public $background_colors = [
        "black" => "40",
        "red" => "41",
        "green" => "42",
        "yellow" => "43",
        "blue" => "44",
        "magenta" => "45",
        "cyan" => "46",
        "light_gray" => "47",
    ];

    /**
     * Console constructor.
     * @param Control $bot
     */
    public function __construct(Control $bot) {
        $this->bot = $bot;
    }

    /**
     * @param string $title
     * @param string|null $subtitle
     */
    public function log(string $title = "", string $subtitle = null) {
        echo PHP_EOL . self::color("[Лог] > " . date("d.m.y, H:i:s") . "> ", "blue") . self::color($title, "white");
        if (isset($subtitle))
            echo PHP_EOL . "\t" . self::color($subtitle, "green");
    }

    /**
     * @param string $title
     * @param string $subtitle
     */
    public function error(string $title = "", string $subtitle = null) {
        echo PHP_EOL . self::color("[Ошибка] > " . date("d.m.y, H:i:s") . "> ", "red") . self::color($title, "white");
        if (isset($subtitle))
            echo PHP_EOL . "\t" . self::color($subtitle, "green");
    }

    /**
     * @param string $title
     * @param string $subtitle
     */
    public function warning(string $title = "", string $subtitle = null) {
        echo PHP_EOL . self::color("[Предупреждение] > " . date("d.m.y, H:i:s") . "> ", "yellow") . self::color($title, "white");
        if (isset($subtitle))
            echo PHP_EOL . "\t" . self::color($subtitle, "green");
    }

    /**
     * @param string $title
     * @param string $subtitle
     */
    public function debug(string $title = "", string $subtitle = null) {
        if ($this->bot->debug == 1) {
            echo PHP_EOL . self::color("[Дебаг] > " . date("d.m.y, H:i:s") . "> ", "purple") . self::color($title, "white");
            if (isset($subtitle))
                echo PHP_EOL . "\t" . self::color($subtitle, "light_purple");
        }
    }

    /**
     * @param string $string
     * @param string $foreground_color
     * @param string $background_color
     * @return string
     */
    public function color(string $string, string $foreground_color = null, string $background_color = null) {
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
