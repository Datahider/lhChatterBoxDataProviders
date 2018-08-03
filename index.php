<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('LH_LIB_ROOT', '/Users/user/MyData/phplib');
define('LH_SESSION_DIR', '/Users/user/MyData/lhsessiondata/');
date_default_timezone_set('UTC');

require_once 'lhChatterBoxDataProviders/classes/lhSessionFile.php';
require_once 'lhChatterBoxDataProviders/classes/lhAIML.php';
require_once 'lhChatterBoxDataProviders/classes/lhCSML.php';
require_once LH_LIB_ROOT . '/lhTextConv/lhTextConv.php';
require_once LH_LIB_ROOT . '/lhValidator/classes/lhEmailValidator.php';


$tags = [
    '#business #Money', [ 'business', 'Money' ],
    '', [],
    ['live', 'family'], [ 'live', 'family' ],
    '#math', [ 'math' ]
];

$category_tags = [
    [ 'Business', 'money' ], true,
    [ 'swiming' ], true,
    [ 'live' ], false,
    [], false
];

$dialogs = [
    "Привет", "", "100.000000", "Привет", ["Привет, коль не шутишь!", "Привет.", "Здрасьте"],
    "Здаров!", "#oficial", "052.631579", "Здравствуйте", ["Добрый день", "Рад приветствовать!"],
    "Любая фигня", "#anyway", "000.000000", "", ["Я бы не хотел сейчас об этом говорить...", "Хм...", "Кстати, у тебя нет знакомого бухгалтера?"]
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
    $split = $aiml->splitTags($tags[$i]);
    foreach ($tags[$i+1] as $key => $value) {
        if ($split[$key] != $value) {
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
    if ($aiml->hasTags($tags[$i*2], $category) != $category_tags[$i*2+1]) {
        echo "FAIL!!! Теги:\n";
        print_r($tags[$i*2]);
        echo ('Ожидалось: ' . ($category_tags[$i*2+1] ? 'true' : 'false'));
        echo "\n";
        die();
    }
    echo '.';
    $i++;
}
echo "Ok\n";

echo 'Загрузка testAiml.xml...';
$aiml->loadAiml('testAiml.xml');
echo "Ok\n";

echo 'Проверка bestMatches($text, $tags=[], $minhitratio=0)';
for($i=0; isset($dialogs[$i]); $i += 5) {
    $result = $aiml->bestMatches($dialogs[$i], $dialogs[$i+1]);
    if (!count($result)) {
        echo "FAIL!!! - bestMatches(...) вернул пустой массив";
        die();
    }
    echo '.';

    foreach ($result as $key => $value) {
        if ($key != $dialogs[$i+2]) {
            echo "FAIL!!! - \$i=$i Получено: \"$key\". Ожидалось: ".$dialogs[$i+2];
            die();
        }
        echo '.';
        if ($value[0] != $dialogs[$i+3]) {
            echo "FAIL!!! - \$i=$i Получено: \"$value[0]\". Ожидалось: ".$dialogs[$i+3];
            die();
        }
        echo '.';
        
        foreach ($value[1]->template as  $template) {
            $awaiting_template = array_shift($dialogs[$i+4]);
            if ((string)$template->text != $awaiting_template) {
                echo "FAIL!!! - \$i=$i Получено: \"$template\". Ожидалось: \"$awaiting_template\"";
                die();
            }
            echo '.';
        }
        if (count($dialogs[$i+4])) {
            echo "FAIL!!! - не получены значения:";
            print_r($dialogs[$i+4]);
            print_r($value[1]);
            die();
        }
        break; // тестим только первое попадание
    }
}
echo "Ok\n";

echo "\nПроверка lhCSML\n";
echo 'Проверка csmlCheck()';

$cs = new lhCSML();
$cs->loadCsml('testCsml.xml');
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
echo "Ok\n";
