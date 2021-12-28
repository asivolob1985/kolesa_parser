<?php

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/telegram.php";

$telegram = new telegram();
$telegram->message_to_telegram('test');