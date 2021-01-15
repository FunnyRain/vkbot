<?php

class Debugger {

    public $object = [];

    public function __construct(array $object) {
        $this->object = $object;
    }

    /**
     * Выводит айди стикера, который вы отправили боту
     * @return string   Айди стикера
     */
    public function getStickerId(): string {
        $object = $this->object;
        $sticker_id = "Debugger Result: \n";
        if (isset($object['attachments'])) {
            foreach ($object['attachments'] as $stickers) {
                if ($stickers['type'] == 'sticker')
                    $sticker_id .= "* Sticker_id: " . $stickers['sticker']['sticker_id'] . PHP_EOL;
            }
        }
        return $sticker_id;
    }

    /**
     * Выводит айди чата
     * @return string   Айди чата
     */
    public function getPeerId(): string {
        $object = $this->object;
        $peer_id = "Debugger Result: \n";
        if (isset($object['peer_id'])) {
            $peer_id .= "* Peer_id: " . $object['peer_id'] . PHP_EOL;
        }
        return $peer_id;
    }
}
