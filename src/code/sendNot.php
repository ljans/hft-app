<?php
require "notification.php";
require "db.php";
require "config.php";
Notification::sendNotification(new DB(Config::DB_USER, Config::DB_PASS, Config::DB_NAME),
    "82wach1bif",
    [
        'title' => 'Neue Prüfungsergebnisse',
        'text' => "123",
        'href' => 'exams',
    ],
    "exam");

?>
