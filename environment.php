<?php

function buscaEnv($dir)
{

    if (!file_exists($dir . '/.env')) {
        return false;
    }
    $lines = file($dir . '/.env');

    foreach ($lines as $line) {
        putenv(trim($line));
    }
}

//código para receber 
// require_once __DIR__ .'/../environment.php';
//     $lines = buscaEnv(__DIR__.SLASH.'..'.SLASH);
//     foreach($lines as $line){
//         putenv(trim($line));
//     }