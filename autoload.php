<?php 

// Автозаполнение
foreach (glob(__DIR__ . '/src/*') as $filename) {
    require_once $filename;
}

?>