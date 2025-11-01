<?php

// config/log.php
return [
    'log_channel' => 'app',
    'log_path' => __DIR__ . '/../storage/logs',
    'logSize' => 30720.6,	//5MB 5*1024*1024 =5242880
    'logKeepDays' => 30,	//30天

];