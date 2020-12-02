<?php 

class KeyboardBuilder {

    public $bot;
    public $keyboard = [];
    public $buttons = [];
    public $isUseKeyboard = false;

    public function __construct(Bot $bot) {
        $this->bot = $bot;
    }

    /**
     * Создаёт клавиатуру
     * @link https://vk.com/dev/bots_docs_3 
     * 
     * @param array $keyboard   Кнопки
     * @param boolean $one_time Скрытие клавиатуры
     * @param boolean $inline   Клавиатура внутри сообщения
     * @return void             Вернет клавиатуру
     */
    public function create(array $keyboard = [], bool $one_time = false, bool $inline = false) {
        foreach ($keyboard as $kfd => $kv) {
            $this->buttons[] = $kv;
        }
        $this->keyboard = ['one_time' => $one_time, 'inline' => $inline, 'buttons' => $this->buttons];
    }

    /**
     * Вывод клавиатуры
     * @return void Вернет клавиатуру для 'keyboard' в сообщении
     */
    public function get() {
        return json_encode($this->keyboard, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Добавляет стандартную кнопку
     * @param string $text      Текст кнопки
     * @param string $color     Цвет кнопки
     * @param string $payload   Дополнительная информация
     * @return array            Вернет кнопку
     */
    public function button(string $text, string $color = 'default', string $payload = ''): array {
        return [
            'action' => [
                'type' => 'text',
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'label' => $text],
            'color' => self::replaceColor($color)
        ];
    }

    /**
     * Кнопка с ссылкой
     * @param string $text  Текст кнопки
     * @param string $link  Ссылка
     * @return array         Вырнет кнопку с ссылкой
     */
    public function link(string $text, string $link): array {
        return [
            'action' => [
                'type' => 'open_link',
                'link' => $link,
                'label' => $text]
        ];
    }

    /**
     * Кнопка с запросом геолокации
     * @return array Вернет кнопку с геолокацией
     */
    public function location(): array {
        return [
            'action' => [
                'type' => 'location'
            ]
        ];
    }

    /**
     * Кнопка оплаты
     * @link https://vk.com/dev/bots_docs_3
     * 
     * @param string $hash  параметры платежа VK Pay и идентификатор приложения
     * @return array
     */
    public function vkpay(string $hash): array {
        return [
            'action' => [
                'type' => 'vkpay',
                'hash' => $hash
            ]
        ];
    }

    /**
     * Кнопка открытия приложения
     * @param string $text      Текст кнопки
     * @param integer $app_id   Айди вызываемого приложения с типом VK Apps.
     * @param integer $owner_id Айди сообщества, в котором установлено приложение, если требуется открыть в контексте сообщества
     * @param string $hash      Хэш для навигации в приложении, будет передан в строке параметров запуска после символа #
     * @return array            Вернет кнопку открытия приложения
     */
    public function vkapps(string $text, int $app_id, int $owner_id, string $hash = ''): array {
        return [
            'action' => [
                'type' => 'open_app',
                'app_id' => $app_id,
                'owner_id' => $owner_id,
                'label' => $text,
                'hash' => (empty($hash)) ? null : $hash
            ]
        ];
    }

    /**
     * Убирает клавиатуру из диалога
     * @return array
     */
    public function remove(): string {
        unset($this->keyboard);
        unset($this->buttons);
        return json_encode(['one_time' => true, 'buttons' => []], JSON_UNESCAPED_UNICODE);
    }

    static function replaceColor(string $color): string {
        return str_replace(['red', 'green', 'white', 'blue'], ['negative', 'positive', 'default', 'primary'], $color);
    }

}
