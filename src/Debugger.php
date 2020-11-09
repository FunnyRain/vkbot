<?php 

class Debugger {

    public array $object = [];

    public function __construct(array $object) {
        $this->object = $object;
    }

    /**
     * Выводит айди стикера, который вы отправили боту
     * @return string   Айди стикера
     */
    public function getStickerId(): string {
        $object = $this->object;
        $sticker_ids = "Debugger Result: \n";
        if (isset($object['attachments'])) {
            foreach ($object['attachments'] as $stickers) {
                if ($stickers['type'] == 'sticker')
                    $sticker_ids .= "* Sticker_id: " . $stickers['sticker']['sticker_id'] . PHP_EOL;
            }
        }
        return $sticker_ids;
    }
}