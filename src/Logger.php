<?php

class Logger {

    /** Папка с логами */
    public string $default_path = __DIR__ . '/../logs/';
    /** --------- */
    public bool $isWrite = false;
    /** --------- */
    public array $foreground_colors = [
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
    /** --------- */
    public array $background_colors = [
        "black" => "40",
        "red" => "41",
        "green" => "42",
        "yellow" => "43",
        "blue" => "44",
        "magenta" => "45",
        "cyan" => "46",
        "light_gray" => "47",
    ];

    public function __construct() {
        if (!file_exists($this->default_path)) {
            if (mkdir($this->default_path, 0777)) {
                $this->isWrite = true;
            } else $this->error('Не удалось создать папку с логами', 'Все логи будут выводиться в консоль!');
        } else $this->isWrite = true;
    }

    /**
     * Записывает в файл с логами
     * @param string $log
     * @return void
     */
    public function writeLog(string $log = ''): void {
        file_put_contents($this->default_path . date("d.m.y") . '.txt', $log, FILE_APPEND);
        echo $log;
    }

    /**
     * Вывод в консоль (Для стандартных сообщений)
     * @param string $title
     * @param string $subtitle
     * @return void
     */
    public function log(string $title = "", string $subtitle = null): void {
        $log = PHP_EOL . $this->color("[Лог] > " . date("d.m.y, H:i:s") . "> ", "blue") . $this->color($title, "white");
        if (isset($subtitle))
            $log .= PHP_EOL . "\t" . $this->color($subtitle, "green");
        if ($this->isWrite)
            $this->writeLog($log);
        else
            echo $log;
    }

    /**
     * Вывод в консоль (Для сообщений с ошибками)
     * @param string $title
     * @param string $subtitle
     * @return void
     */
    public function error(string $title = "", string $subtitle = null): void {
        $log = PHP_EOL . $this->color("[Ошибка] > " . date("d.m.y, H:i:s") . "> ", "red") . $this->color($title, "white");
        if (isset($subtitle))
            $log .= PHP_EOL . "\t" . $this->color($subtitle, "green");
        if ($this->isWrite)
            $this->writeLog($log);
        else
            echo $log;
    }

    /**
     * Вывод в консоль (Для сообщений с предупреждением)
     * @param string $title
     * @param string $subtitle
     * @return void
     */
    public function warning(string $title = "", string $subtitle = null): void {
        $log = PHP_EOL . $this->color("[Предупреждение] > " . date("d.m.y, H:i:s") . "> ", "yellow") . $this->color($title, "white");
        if (isset($subtitle))
            $log .= PHP_EOL . "\t" . $this->color($subtitle, "green");
        if ($this->isWrite)
            $this->writeLog($log);
        else
            echo $log;
    }

    /**
     * Вывод в консоль (Для сообщений разработчикам)
     * @param string $title
     * @param string $subtitle
     * @return void
     */
    public function debug(string $title = "", string $subtitle = null): void {
        if ($this->bot->debug == 1) {
            echo PHP_EOL . $this->color("[Дебаг] > " . date("d.m.y, H:i:s") . "> ", "purple") . $this->color($title, "white");
            if (isset($subtitle))
                echo PHP_EOL . "\t" . $this->color($subtitle, "light_purple");
        }
    }

    /**
     * Пример: color('желтый текст с черным фоном', 'yellow', 'black');
     * @param string $string
     * @param string $foreground_color
     * @param string $background_color
     * @return string
     */
    public function color(string $string, string $foreground_color = null, string $background_color = null): string {
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
