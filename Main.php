<?php

require_once "autoload.php";

ini_set('display_errors', 1);
date_default_timezone_set('Europe/Moscow');

$bot = new Control(
    "токен",
    "айди группы",
    5.102
);

$bot->getRequest();
$bot->message->sendMessage("test", 439239695);