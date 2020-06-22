<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhAIML
 *
 * @author user
 */
require_once __DIR__ . '/../abstract/lhAbstractAIML.php';

class lhAIML extends lhAbstractAIML {

    public function bestMatches($text, $tags=[], $minhitratio=0) {
        $this->log(__CLASS__.'->'.__FUNCTION__);
        $this->log('$text='. print_r($text, true), 15);
        $this->log('$tags='. print_r($tags, true), 15);
        $this->log('$minhitratio='. print_r($minhitratio, true), 15);
        $result = [];
        $metaphone = lhTextConv::metaphone($text);
        $aiml = $this->getAiml();
        $tags = $this->splitTags($tags);
        foreach ($aiml->category as $category) {
            if (!$this->hasTags($tags, $category)) {
                continue;
            }
            $category_patterns = $this->patternArray($category);
            $this->log('$category_patterns='. print_r($category_patterns, true), 20);
            $match = lhTextConv::bestMatch($category_patterns, $text, $percentage);
            $this->log('$match='. print_r($match, true), 20);
            if ($percentage >= $minhitratio) {
                $index = sprintf("%010.6f", $percentage);
                $result[$index][0] = $category_patterns[$match];
                $result[$index][1] = $category;
                $result[$index]['category'] = $category;
                $result[$index]['best_match'] = $category_patterns[$match];
                $result[$index]['match_level'] = $percentage;
            }
        }
        krsort($result);
        return $result;
    }
    
    // splitTags($tags)
    // Служебная функция превращающая строку хештегов в массив без #
    // Если на вход передан массив - его и возвращает
    //
    public function splitTags($tags) {
        $this->log(__CLASS__.'->'.__FUNCTION__);
        if (is_scalar($tags)) {
            $tags = preg_split("/\s*#/", $tags, -1, PREG_SPLIT_NO_EMPTY);
        }
        return $tags;
    }
    
    public function hasTags($tags, $category) {
        $this->log(__CLASS__.'->'.__FUNCTION__);
        $tags = $this->splitTags($tags);
        $this->log($tags);
        $this->log($category);
        $has_tag = true;
        foreach ($tags as $tag) {
            $has_tag = false;
            foreach ($category->tag as $category_tag) {
                if (mb_strtolower($category_tag) == mb_strtolower($tag)) {
                    $has_tag = true;
                    break;
                }
            }
            if (!$has_tag) { 
                $this->log("Has no tag $tag");
                break; 
            }
        }
        return $has_tag;
    }
    
    protected function patternArray($category) {
        $result = [];
        foreach ($category->pattern as $pattern) {
            $result[] = (string)$pattern;
        }
        return $result;
     }

    protected function _test_data() {
        return [
            'aimlFromString' => [[<<<END
<?xml version="1.0" encoding="UTF-8"?>
<aiml>
    <category name="Тестирование тегов">
        <tag>тест</tag>
        <tag>тестовый</tag>
        <tag>тестирование</tag>
        <pattern>Тест</pattern>
    </category>
    <category name="Приветствие_0">
        <tag>unformal</tag>
        <tag>fullmatch</tag>
        <pattern>Привет</pattern>
        <pattern>Здарова!</pattern>
        <template>
            <text>Привет, коль не шутишь!</text>
        </template>
        <template>
            <text>Привет.</text>
        </template>
        <template>
            <text>Здрасьте</text>
        </template>
    </category>
    <category name="Приветствие_1">
        <tag>fullmatch</tag>
        <tag>oficial</tag>
        <pattern>Добрый день</pattern>
        <pattern>Здравствуйте</pattern>
        <template>
            <text>Добрый день</text>
        </template>
        <template>
            <text>Рад приветствовать!</text>
        </template>
    </category>
    <category name="Anyway">
        <tag>fullmatch</tag>
        <description>
            Категория содержащая ответы на все случаи жизни. 
            Фильтруется по тегу #anyway
        </description>
        <pattern></pattern>
        <tag>anyway</tag>
        <template>
            <text>Я бы не хотел сейчас об этом говорить...</text>
        </template>
        <template>
            <text>Хм...</text>
        </template>
        <template>
            <text>Кстати, у тебя нет знакомого бухгалтера?</text>
        </template>
    </category>
    <category name="Ответы на как дела_0">
        <tag>fullmatch</tag>
        <tag>какдела</tag>
        <pattern>Норм</pattern>
        <pattern>Нормально</pattern>
        <pattern>Отлично</pattern>
        <pattern>Не плохо</pattern>
        <pattern>Хорошо</pattern>
        <template>
            <text>Это хорошо. У меня тоже не плохо</text>
        </template>
        <template>
            <text>Да, у меня тоже не плохо.</text>
        </template>
    </category>
    <category name="%GREETING%" match_type="start">
        <tag>startmatch</tag>
        <pattern>Здравствуйте</pattern>
        <pattern>Добрый день</pattern>
        <pattern>Привет</pattern>
        <pattern>Доброе утро</pattern>
        <pattern>Добрый вечер</pattern>
        <pattern>Трям</pattern>
    </category>
</aiml>
END
            , null]],
            'getAiml' => [[new lhTest(lhTest::IS_A, 'SimpleXMLElement')]],
            'setAiml' => '_test_skip_', // проверено в aimlFromString
            'loadAiml' => '_test_skip_',
            'patternArray' => [
                [new SimpleXMLElement("<root><pattern>aaa</pattern><pattern>ccc</pattern><pattern>bbb</pattern></root>"), ['aaa', 'ccc', 'bbb']]
            ],
            'splitTags' => [
                ['#business #Money', [ 'business', 'Money' ]],
                ['', []],
                [['live', 'family'], [ 'live', 'family' ]],
                ['#math', [ 'math' ]]
            ],
            'hasTags' => [
                ['#тест #тестирование #тестовый', $this->getAiml()->category, true],
                ['#погода', $this->getAiml()->category, false],
                [['тест', 'тестирование'], $this->getAiml()->category, true],
                [['тестирование', 'погода'], $this->getAiml()->category, false],
                ['#тест #погода', $this->getAiml()->category, false],
                ['#тест#тестирование', $this->getAiml()->category, true]
            ],
            'bestMatches' => [  // простое тестирование
                [
                    "привет", ['fullmatch'], 50, 
                    new lhTest(function($result){
                        if (is_a($result, "Exception")) {
                            throw $result;
                        }
                        if ($result['100.000000'][0] != "Привет") {
                            throw new Exception("Ожидалось найти Привет. Нашлось ". print_r($result, true));
                        }
                    })
                ],
                [
                    "Здаров!", "#oficial#fullmatch",
                    new lhTest(function ($result){
                        if ($result['052.631579'][0] != "Здравствуйте") {
                            throw new Exception("Ожидалось найти Здравствуйте. Нашлось ". print_r($result, true));
                        }
                    })
                ],
                [
                    "Любая фигня", "#anyway#fullmatch",
                    new lhTest(function ($result){
                        if ($result['000.000000'][0] != "") {
                            throw new Exception("Ожидалось найти пустой ответ. Нашлось ". print_r($result, true));
                        }
                    })
                ],
            ],
        ];
    }
}
