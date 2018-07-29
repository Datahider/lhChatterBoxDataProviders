<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('LH_LIB_ROOT', './');
define('LH_SESSION_DIR', '/Users/user/MyData/lhsessiondata/');
date_default_timezone_set('UTC');

require_once LH_LIB_ROOT . 'lhChatterBoxDataProviders/classes/lhSessionFile.php';

$n = new lhSessionFile('123');

echo 'Проверка записи и чтения внутри сессии..............';
$random_value = rand();
$n->set('status', $random_value);
$status = $n->get('status');
echo $status == $random_value ? "Ok\n" : "FAIL!!!\n";

echo 'Проверка записи и чтения лог-файла..................';
$n->log(lhSessionFile::$facility_log, 'Строка лог файла');
$log = $n->readLog(lhSessionFile::$facility_log, 1);
echo preg_match("/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{4}: Строка лог файла$/u", $log[0]) ? "Ok\n" : "FAIL!!! - Got: $log[0]\n";

echo 'Проверка записи и чтения между сессиями.............';
$n2 = new lhSessionFile('123');
$status2 = $n2->get('status');
echo $status2 == $status ? "Ok\n" : "FAIL!!! - Got: $status2\n";
