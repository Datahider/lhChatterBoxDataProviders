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
require_once LH_LIB_ROOT . 'lhChatterBoxDataProviders/classes/lhAIML.php';

$tags = [
    '#business #money', [ 'business', 'money' ],
    '', [],
    ['live', 'family'], [ 'live', 'family' ],
    '#math', [ 'math' ]
];

$category_tags = [
    [ 'business', 'money' ], true,
    [ 'swiming' ], true,
    [ 'live' ], false,
    [], false
];

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

echo "\nПроверка lhAIML\n";
echo 'Проверка splitTags($tags)';
$aiml = new lhAIML();
for ($i=0; isset($tags[$i]); $i += 2) {
    $spit = $aiml->splitTags($tags[$i]);
    foreach ($tags[$i+1] as $key => $value) {
        if ($spit[$key] != $value) {
            echo "FAIL!!! - Получено: \"$split[$key]\", ожидалось: \"$value\"";
            die();
        }
    }
    echo '.';
}
echo "Ok\n";

echo 'Проверка hasTags($tags, $category)';
// Сначала заполним категории
for ($i=0; isset($category_tags[$i]); $i += 2) {
    $category = $aiml->getAiml()->addChild('category');
    foreach ($category_tags[$i] as $tag) {
        $category->addChild('tag', $tag);
    }
}

// Теперь тестируем
$i = 0;
foreach ($aiml->getAiml()->category as $category) {
    if ($aiml->hasTags($tags[$i*2+1], $category) != $category_tags[$i*2+1]) {
        echo "FAIL!!! Теги:\n";
        print_r($tags[$i*2+1]);
        echo ('Ожидалось: ' . ($category_tags[$i*2+1] ? 'true' : 'false'));
        echo "\n";
        die();
    }
    echo '.';
    $i++;
}
echo "Ok\n";
