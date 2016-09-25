<?php

return [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=192.168.40.157;dbname=tidy',
        'username' => 'tidy',
        'password' => '24tidy.com',
        'charset' => 'utf8'
    ],
    //人事库
    'dbhr'=>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=192.168.40.157;dbname=tidyhumanresources',
        'username' => 'tidy',
        'password' => '24tidy.com',
        'charset' => 'utf8',
    ],
];
