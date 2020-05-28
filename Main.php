<?php

require_once "autoload.php";

ini_set('display_errors', 1);
date_default_timezone_set('Europe/Moscow');

$bot = new Control(
    "токен",
    "айди группы"
);
$mid = [];

while (true) {
    $bot->start();
    $text = isset($bot->get["text"]) ? $bot->get["text"] : null;
    $from_id = isset($bot->get["from_id"]) ? $bot->get["from_id"] : null;
    $peer_id = isset($bot->get["peer_id"]) ? $bot->get["peer_id"] : null;
    $message_id = isset($bot->get["conversation_message_id"]) ? $bot->get["conversation_message_id"] : null;
    $attachments = isset($bot->get["attachments"]) ? $bot->get["attachments"] : null;
    if (!isset($mid[$peer_id])) $mid[$peer_id][] = -1;
    if (!in_array($message_id, $mid[$peer_id])) {
        $mid[$peer_id][] = $message_id;
        ////////////////////////
        /* ТУТ ВАШ КОД */
        $bot->message->sendMessage($text, $peer_id);
        ////////////////////////
    }
}