<?php

use Ulib\Grabber\Serializer\Serializer;

include_once(__DIR__ . '/src/UlibPhoneDirectory.php');
include_once(__DIR__ . '/src/Serializer/Serializer.php');
$ulibPhoneDirectory = new UlibPhoneDirectory();
echo '<pre>';
var_dump(Serializer::array($ulibPhoneDirectory->getUsers()));
echo '</pre>';

echo '<pre>';
var_dump($ulibPhoneDirectory->getPaginator());
echo '</pre>';

echo '<pre>';
var_dump($ulibPhoneDirectory->getPageResult());
echo '</pre>';
