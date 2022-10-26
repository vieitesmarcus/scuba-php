<?php
$count = 0;

for($i = 0; $i < 10; $i++){
    $count += $i;
    echo $i .PHP_EOL;
}
echo __DIR__;
echo $count .PHP_EOL;
echo $i .PHP_EOL;