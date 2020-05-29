<?php

class Config {
    const DETECT = -1; //Detect by file extension
    const PROPERTIES = 0; // .properties
    const CNF = Config::PROPERTIES; // .cnf
    const JSON = 1; // .js, .json
    const YAML = 2; // .yml, .yaml
    const SERIALIZED = 4; // .sl
    const ENUM = 5; // .txt, .list, .enum

    /**
     * @var array
     */
    private $config = [];
    /**
     * @var string
     */
    private $file;
    /**
     * @var bool
     */
    private $correct = false;
    /**
     * @var int
     */
    private $type = Config::DETECT;

    public static $formats = [
        "properties" => Config::PROPERTIES,
        "cnf" => Config::CNF,
        "conf" => Config::CNF,
        "config" => Config::CNF,
        "json" => Config::JSON,
        "js" => Config::JSON,
        "yml" => Config::YAML,
        "yaml" => Config::YAML,
        "sl" => Config::SERIALIZED,
        "serialize" => Config::SERIALIZED,
        "txt" => Config::ENUM,
        "list" => Config::ENUM,
        "enum" => Config::ENUM,
    ];

    /**
     * Config constructor.
     * @param $file
     * @param int $type
     * @param array $default
     * @param null $correct
     */
    public function __construct($file, $type = Config::DETECT, $default = [], &$correct = null) {
        $this->load($file, $type, $default);
        $correct = $this->correct;
    }

    /**
     * @param $str
     * @return string|string[]|null
     */
    public static function fixYAMLIndexes($str) {
        return preg_replace("#^([ ]*)([a-zA-Z_]{1}[^\:]*)\:#m", "$1\"$2\":", $str);
    }

    /**
     * @param $file
     * @param int $type
     * @param array $default
     * @return bool
     */
    public function load($file, $type = Config::DETECT, $default = []) {
        $this->correct = true;
        $this->type = (int)$type;
        $this->file = $file;
        if (!is_array($default)) {
            $default = [];
        }
        if (!file_exists($file)) {
            $this->config = $default;
            $this->save();
        } else {
            if ($this->type === Config::DETECT) {
                $extension = explode(".", basename($this->file));
                $extension = strtolower(trim(array_pop($extension)));
                if (isset(Config::$formats[$extension])) {
                    $this->type = Config::$formats[$extension];
                } else {
                    $this->correct = false;
                }
            }
            if ($this->correct === true) {
                $content = @file_get_contents($this->file);
                switch ($this->type) {
                    case Config::PROPERTIES:
                    case Config::CNF:
                        $this->parseProperties($content);
                        break;
                    case Config::JSON:
                        $this->config = json_decode($content, true);
                        break;
                    case Config::YAML:
                        $content = self::fixYAMLIndexes($content);
                        $this->config = yaml_parse($content);
                        break;
                    case Config::SERIALIZED:
                        $this->config = unserialize($content);
                        break;
                    case Config::ENUM:
                        $this->parseList($content);
                        break;
                    default:
                        $this->correct = false;
                        return false;
                }
                if (!is_array($this->config)) {
                    $this->config = $default;
                }
                if ($this->fillDefaults($default, $this->config) > 0) {
                    $this->save();
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function save() {
        if ($this->correct === true) {
            $content = null;
            switch ($this->type) {
                case Config::PROPERTIES:
                case Config::CNF:
                    $content = $this->writeProperties();
                    break;
                case Config::JSON:
                    $content = json_encode($this->config, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
                    break;
                case Config::YAML:
                    $content = yaml_emit($this->config, YAML_UTF8_ENCODING);
                    break;
                case Config::SERIALIZED:
                    $content = @serialize($this->config);
                    break;
                case Config::ENUM:
                    $content = implode("\r\n", array_keys($this->config));
                    break;
            }
            @file_put_contents($this->file, $content, LOCK_EX);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $k
     * @return bool|mixed
     */
    public function __get($k) {
        return $this->get($k);
    }

    /**
     * @param $k
     * @param $v
     */
    public function __set($k, $v) {
        $this->set($k, $v);
    }

    /**
     * @param $k
     * @return bool
     */
    public function __isset($k) {
        return $this->exists($k);
    }

    /**
     * @param $k
     */
    public function __unset($k) {
        $this->remove($k);
    }

    /**
     * @param $key
     * @param $value
     */
    public function setNested($key, $value) {
        $vars = explode(".", $key);
        $base = array_shift($vars);

        if (!isset($this->config[$base])) {
            $this->config[$base] = [];
        }

        $base =& $this->config[$base];

        while (count($vars) > 0) {
            $baseKey = array_shift($vars);
            if (!isset($base[$baseKey])) {
                $base[$baseKey] = [];
            }
            $base =& $base[$baseKey];
        }

        $base = $value;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getNested($key, $default = null) {
        $vars = explode(".", $key);
        $base = array_shift($vars);
        if (isset($this->config[$base])) {
            $base = $this->config[$base];
        } else {
            return $default;
        }

        while (count($vars) > 0) {
            $baseKey = array_shift($vars);
            if (is_array($base) and isset($base[$baseKey])) {
                $base = $base[$baseKey];
            } else {
                return $default;
            }
        }

        return $base;
    }

    /**
     * @param $k
     * @param bool $default
     * @return bool|mixed
     */
    public function get($k, $default = false) {
        return ($this->correct and isset($this->config[$k])) ? $this->config[$k] : $default;
    }

    /**
     * @param $path
     * @return array|mixed|null
     */
    public function getPath($path) {
        $currPath =& $this->config;
        foreach (explode(".", $path) as $component) {
            if (isset($currPath[$component])) {
                $currPath =& $currPath[$component];
            } else {
                $currPath = null;
            }
        }
        return $currPath;
    }

    /**
     * @param $path
     * @param $value
     */
    public function setPath($path, $value) {
        $currPath =& $this->config;
        $components = explode(".", $path);
        $final = array_pop($components);
        foreach ($components as $component) {
            if (!isset($currPath[$component])) {
                $currPath[$component] = [];
            }
            $currPath =& $currPath[$component];
        }
        $currPath[$final] = $value;
    }

    /**
     * @param $k
     * @param bool $v
     */
    public function set($k, $v = true) {
        $this->config[$k] = $v;
    }

    /**
     * @param $v
     */
    public function setAll($v) {
        $this->config = $v;
    }

    /**
     * @param $k
     * @param bool $lowercase
     * @return bool
     */
    public function exists($k, $lowercase = false) {
        if ($lowercase === true) {
            $k = strtolower($k);
            $array = array_change_key_case($this->config, CASE_LOWER);
            return isset($array[$k]);
        } else {
            return isset($this->config[$k]);
        }
    }

    /**
     * @param $k
     */
    public function remove($k) {
        unset($this->config[$k]);
    }

    /**
     * @param bool $keys
     * @return array
     */
    public function getAll($keys = false) {
        return ($keys === true ? array_keys($this->config) : $this->config);
    }

    /**
     * @param array $defaults
     */
    public function setDefaults(array $defaults) {
        $this->fillDefaults($defaults, $this->config);
    }

    /**
     * @param $default
     * @param $data
     * @return int|mixed
     */
    private function fillDefaults($default, &$data) {
        $changed = 0;
        foreach ($default as $k => $v) {
            if (is_array($v)) {
                if (!isset($data[$k]) or !is_array($data[$k])) {
                    $data[$k] = [];
                }
                $changed += $this->fillDefaults($v, $data[$k]);
            } elseif (!isset($data[$k])) {
                $data[$k] = $v;
                ++$changed;
            }
        }
        return $changed;
    }

    /**
     * @param $content
     */
    private function parseList($content) {
        foreach (explode("\n", trim(str_replace("\r\n", "\n", $content))) as $v) {
            $v = trim($v);
            if ($v == "") {
                continue;
            }
            $this->config[$v] = true;
        }
    }

    /**
     * @return string
     */
    private function writeProperties() {
        $content = "#Properties Config file\r\n#" . date("D M j H:i:s T Y") . "\r\n";
        foreach ($this->config as $k => $v) {
            if (is_bool($v) === true) {
                $v = $v === true ? "on" : "off";
            } elseif (is_array($v)) {
                $v = implode(";", $v);
            }
            $content .= $k . "=" . $v . "\r\n";
        }
        return $content;
    }

    /**
     * @param $content
     */
    private function parseProperties($content) {
        if (preg_match_all('/([a-zA-Z0-9\-_\.]*)=([^\r\n]*)/u', $content, $matches) > 0) { //false or 0 matches
            foreach ($matches[1] as $i => $k) {
                $v = trim($matches[2][$i]);
                switch (strtolower($v)) {
                    case "on":
                    case "true":
                    case "yes":
                        $v = true;
                        break;
                    case "off":
                    case "false":
                    case "no":
                        $v = false;
                        break;
                }
                if (isset($this->config[$k])) {
                    // todo
                }
                $this->config[$k] = $v;
            }
        }
    }

}
