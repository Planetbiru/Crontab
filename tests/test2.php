<?php

use DivineOmega\SSHConnection\SSHConnection;

require_once dirname(__DIR__) . "/vendor/autoload.php";


$connection = (new SSHConnection())
            ->to('fewf.aaa.com')
            ->onPort(22)
            ->as('root')
            ->withPassword('cebong@aaaa#')
         // ->withPrivateKey($privateKeyPath)
         // ->timeout(0)
            ->connect();

$command = $connection->run('echo "Hello world!"');

$command->getOutput();  // 'Hello World'
$command->getError();   // ''

$connection->upload($localPath, $remotePath);
$connection->download($remotePath, $localPath);