<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];


require_once $_SERVER["DOCUMENT_ROOT"]."/php_interface/parser/classes/kd/kd.php";

$dataf = ['product_id' => 7027499];
$kd = kd::search('bDOdluITdq9oW405IK_qTfo9dOJYhmgK', $dataf);
$res = json_decode($kd);
var_dump($res);
 
