<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('LH_LIB_ROOT', '/Users/user/MyData/phplib');
define('LH_SESSION_DIR', '/Users/user/MyData/lhsessiondata/');
date_default_timezone_set('UTC');

require_once LH_LIB_ROOT . '/lhTestingSuite/classes/lhSelfTestingClass.php';
require_once LH_LIB_ROOT . '/lhTestingSuite/classes/lhTest.php';
require_once LH_LIB_ROOT . '/lhTextConv/lhTextConv.php';
require_once LH_LIB_ROOT . '/lhValidator/classes/lhEmailValidator.php';
require_once LH_LIB_ROOT . '/lhValidator/classes/lhNameValidator.php';
require_once LH_LIB_ROOT . '/lhRuNames/classes/lhRuNames.php';
require_once 'lhChatterBoxDataProviders/classes/lhSessionFile.php';
require_once 'lhChatterBoxDataProviders/classes/lhAIML.php';
require_once 'lhChatterBoxDataProviders/classes/lhCSML.php';


$n = new lhSessionFile('123');

echo "Проверка lhSessionFile\n";
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

echo 'Проверка удаления сессии.......';
$n2->destroy();

$n3 = new lhSessionFile('123');
$status3 = $n3->get('status');
echo $status3 ? "FAIL!!! - Got: $status3\n" : "Ok\n";

echo "\nПроверка lhAIML\n";
$aiml = new lhAIML('testAiml.xml');
$aiml->_test();

echo "\nПроверка lhCSML\n";
echo 'Проверка csmlCheck()';

$cs = new lhCSML('testCsml.xml');
$check_result = $cs->csmlCheck();
if (count($check_result)) {
    echo ".FAIL!!! - Не найдены блоки\n";
    print_r($check_result);
} else {
    echo ".Ok\n";
}

echo 'Проверка block($name=null)';
$block = $cs->block();
if ($block['name'] != 'start') {
    echo "FAIL!!! - Получено \"$block[name]\", ожидалось \"start\"";
    die();
}
echo '.';

$block = $cs->block('Не хочет называть имя');
if ($block['name'] != 'Не хочет называть имя') {
    echo "FAIL!!! - Получено \"$block[name]\", ожидалось \"Не хочет называть имя\"";
    die();
}
echo '.';

if ($block->template != 'Хм... :thinking: Ок. Я буду называть тебя Уася, хорошо?') {
    echo "FAIL!!! - Получено \"$block->template\", ожидалось \"Хм... :thinking: Ок. Я буду называть тебя Уася, хорошо?\"";
    die();
}
echo '.';
echo "Ok\n";

echo 'Проверка answer($user_answer, $minhitratio=0, $name=null)';
$answer = $cs->answer("Не скажу", 0, 'start');
if ($answer->next != 'Не хочет называть имя') {
    echo "FAIL!!! - Получено \"$answer->next\", ожидалось \"Не хочет называть имя\"";
    die();
}
echo '.';
$answer = $cs->answer("Нет", 0, $answer->next);
if ($answer->next != 'Даем возможность исправить имя') {
    echo "FAIL!!! - Получено \"$answer->next\", ожидалось \"Даем возможность исправить имя\"";
    die();
}
echo '.';

$cs->block("Запрос e-mail");
$answer = $cs->answer("boss@o3000.ru");
$validated = json_decode($answer->validated, true);
if ($validated['domain'] != 'o3000.ru') {
    echo "FAIL!!! - Получено \"$validated[domain]\", ожидалось \"o3000.ru\"";
    die();
}
echo '.';

$cs->block("start");
$answer = $cs->answer("петя");
$validated = json_decode($answer->validated, true);
if ($validated['full'] != 'Петр') {
    echo "FAIL!!! - Получено \"$validated[full]\", ожидалось \"Петр\"";
    die();
}
echo '.';
echo "Ok\n";
