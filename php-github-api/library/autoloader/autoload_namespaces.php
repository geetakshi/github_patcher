<?php


$libraryDir = dirname(dirname(__FILE__));
$baseDir = dirname($libraryDir);

return array(
    'Github\\' => array($baseDir . '/lib'),
    'Buzz' => array($libraryDir . '/buzz/lib'),
);
?>