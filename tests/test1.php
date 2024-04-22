<?php

use Crontab\CrontabManager;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$crontab = new CrontabManager("few.test.com", 22, "root", "Coba");
$crontab->exec("date");
echo $crontab->getOutput();
echo $crontab->getReturn();
