<?php

class Console {

    private $foreground_colors = [],
        $background_colors = [];

    public function __construct() {
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dgray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['lblue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['lgreen'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['lcyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['lred'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['lpurple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['lgray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['lgray'] = '47';
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