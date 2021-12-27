<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('BX_NO_ACCELERATOR_RESET', true);
define('CHK_EVENT', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/parsing.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/telegram.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/kolesadarom.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/trektyre.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/fortochki.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/kolesoural.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/debug.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/process.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/properties.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/parser/classes/kd/kd.php";

@set_time_limit(0);
@ignore_user_abort(true);
CAgent::CheckAgents();
CModule::IncludeModule('sale');

$memory = memory_get_usage();
$start = microtime(true);

$kolesadarom_process = true;
$trektyre_process = true;
$fortochki_process = true;
$kolesoural_process = true;

debug::clean();

debug::log('*******************************START********************************');
$telegram = new telegram();
$telegram->message_to_telegram('start parsing');
if ($kolesadarom_process){
    $parser_data_kolesadarom = new kolesadarom();
    $kolesadarom_check_data = $parser_data_kolesadarom->check_data();
    $telegram->message_to_telegram('$kolesadarom_check_data='.$kolesadarom_check_data);
}else{
    $kolesadarom_check_data = true;
}

if ($trektyre_process) {
    $parser_data_trek = new trektyre();
    $trek_check_data = $parser_data_trek->check_data();
    $telegram->message_to_telegram('$trek_check_data='.$trek_check_data);
}else{
    $trek_check_data = true;
}

if ($fortochki_process) {
    $parser_data_fortochki = new fortochki();
    $fortochki_check_data = $parser_data_fortochki->check_data();
    $telegram->message_to_telegram('$fortochki_check_data='.$fortochki_check_data);
}else{
    $fortochki_check_data = true;
}

if ($kolesoural_process) {
    $parser_data_kolesoural = new kolesoural();
    $kolesoural_check_data = $parser_data_kolesoural->check_data();
    $telegram->message_to_telegram('$kolesoural_check_data='.$kolesoural_check_data);
}else{
    $kolesoural_check_data = true;
}

debug::log('checks');

if ($kolesadarom_check_data === false or $trek_check_data === false or $fortochki_check_data === false or $kolesoural_check_data === false) {
    @mail('asivolob1985@gmail.com', 'Error in data for parsing', 'check parsing data!');
    debug::log($kolesadarom_check_data, 'error $kolesadarom_check_data data');
    debug::log($trek_check_data, 'error $trek_check_data data');
    debug::log($fortochki_check_data, 'error $fortochki_check_data data');
    debug::log($kolesoural_check_data, 'error $kolesoural_check_data data');
    debug::log('error parsing data');
    $telegram->message_to_telegram('Error in parsing data');
    $telegram->message_to_telegram('Stop parsing');
    return false;
}

debug::log('off actitvity');//1-если в массиве есть данные, то убираем активность в битриксе
properties::fix_code();//костыль для замены кодов
process::off_activity_type('Tires');
process::off_activity_type('Rims');

if ($fortochki_process) {
    $xml = new SimpleXMLElement($parser_data_fortochki->getData());
    debug::log('---  start parser tyres fortochki  ---');
    $telegram->message_to_telegram('start parser tyres fortochki');
    $parser_data_fortochki->parsing_tyres($xml);
    debug::log('---  end parser tyres fortochki ---');
    $telegram->message_to_telegram('end parser tyres fortochki');
    debug::log('---  start parser Rims fortochki  ---');
    $telegram->message_to_telegram('start parser rims fortochki');
    $parser_data_fortochki->parsing_rims($xml);
    debug::log('---  end parser rims fortochki ---');
    $telegram->message_to_telegram('end parser rims fortochki');
}

if ($kolesadarom_process) {
    $xml = new SimpleXMLElement($parser_data_kolesadarom->getData());
    $telegram->message_to_telegram('start parser rims kolesadarom');
    debug::log('---  start parser Rims kolesadarom  ---');
    $parser_data_kolesadarom->parsing_rims($xml);
    $telegram->message_to_telegram('end parser rims kolesadarom');
    debug::log('---  end parser rims kolesadarom ---');
    debug::log('---  start parser tyres  kolesadarom ---');
    $telegram->message_to_telegram('start parser tyres kolesadarom');
    $parser_data_kolesadarom->parsing_tyres($xml);
    debug::log('---  end parser rims kolesadarom ---');
    $telegram->message_to_telegram('end parser rims kolesadarom');
}

if ($trektyre_process) {
    $xml = new SimpleXMLElement($parser_data_trek->getData());
    debug::log('---  start parser tyres trektyre  ---');
    $telegram->message_to_telegram('start parser tyres trektyre');
    $parser_data_trek->parsing_tyres($xml);
    debug::log('---  end parser tyres trektyre ---');
    $telegram->message_to_telegram('end parser tyres trektyre');
}

if($kolesoural_process){
    $xml = new SimpleXMLElement($parser_data_kolesoural->getData());
    debug::log('---  start parser rims kolesoural  ---');
    $telegram->message_to_telegram('start parser rims kolesoural');
    $parser_data_kolesoural->parsing_rims($xml);
    debug::log('---  end parser rims kolesoural ---');
    $telegram->message_to_telegram('end parser rims kolesoural');
}

$telegram->message_to_telegram('parsing done');

debug::log('---  end parsing  ---');
$time = (microtime(true) - $start) / 60;
$memory = memory_get_usage() - $memory;
$i = 0;
while (floor($memory / 1024) > 0) {
    $i++;
    $memory /= 1024;
}
$name = ['байт', 'КБ', 'МБ'];

debug::log($time.' min.   ####  ');
debug::log(round($memory, 2).' '.$name[$i]);
debug::log('******************************************************************');
$telegram->message_to_telegram("END PARSING to ".$time." min");
echo date('d.m.Y H:i:s')."  END PARSING to ".$time." min\r\n";