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
    const DEBUG_LEVEL = 0;

    public function bestMatches($text, $tags=[], $minhitratio=0) {
        $this->log(__CLASS__.'->'.__FUNCTION__);
        $result = [];
        $metaphone = lhTextConv::metaphone($text);
        $aiml = $this->getAiml();
        $tags = $this->splitTags($tags);
        foreach ($aiml->category as $category) {
            if ($this->hasTags($tags, $category)) {
                $match = $this->categoryBestMatch($category, $metaphone, true);
                if ($match['match_level'] >= $minhitratio) {
                    $index = sprintf("%010.6f", $match['match_level']);
                    $result[$index][0] = $match['best_match'];
                    $result[$index][1] = $category;
                    $result[$index]['best_match'] = $match['best_match'];
                    $result[$index]['match_level'] = $match['match_level'];
                    $result[$index]['match_type'] = $match['match_type'];
                    $result[$index]['match'] = $match['match'];
                }
            }
        }
        krsort($result);
        return $result;
    }
    
    // cacategoryBestMatch
    // Ищет наилучший паттерн категории. 
    // Возвращает массив [
    //  'best_match' => строка паттерна с лучшим результатом,
    //  'match_level' => уровень похожести в процентах,
    // ].
    private function categoryBestMatch($category, $text, $is_metaphone=false) {
        $match_type = $category['match_type'] ? (string)$category['match_type'] : 'full';
        $match = $category['match'] ? (string)$category['match'] : 'full';
        
        switch ($match_type) {
            case 'full': case 'start': case 'end': case 'any':
                break;
            default:
                throw new Exception("Unknown category match_type '$match_type'");
        }

        switch ($match) {
            case 'full': case 'words': case 'chars':
                break;
            default:
                throw new Exception("Unknown category match '$match'");
        }
        $metaphone = $is_metaphone ? $text : lhTextConv::metaphone($text);
        $match_func = "match_". $match_type. "_". $match;
        return $this->$match_func($category, $metaphone);
    }
    
    private function match_return($best_match, $match_level, $match_type, $match) {
        if ($match_level == -1) {
            throw new Exception("No pattern found in category '$category[name]'");
        }
        return [
            'best_match' => $best_match,
            'match_level' => $match_level,
            'match_type' => $match_type,
            'match' => $match
        ];
    }

    private function match_full_full($category, $metaphone) {
        $this->log(__CLASS__.'->'.__FUNCTION__); $microtime = microtime(true);
        $best_match = '';
        $match_level = -1;
        
        foreach ($category->pattern as $pattern) {
            $m_pattern = lhTextConv::metaphone($pattern);
            $level = lhTextConv::metaphoneSimilarity($metaphone, $m_pattern);
            if ($level > $match_level) {
                $match_level = $level;
                $best_match = (string)$pattern;
            }
        }
        $this->log(__CLASS__.'->'.__FUNCTION__.' have taken '.  (microtime(true)-$microtime). ' seconds', 5);
        return $this->match_return($best_match, $match_level, 'full', 'full');
    }

    private function match_start_full($category, $metaphone) {
        $this->log(__CLASS__.'->'.__FUNCTION__); $microtime = microtime(true);
        $best_match = '';
        $match_level = -1;
        
        foreach ($category->pattern as $pattern) {
            $m_pattern = lhTextConv::metaphone($pattern);
            for ($i=1; $i<=strlen($metaphone); $i++) {
                $t_pattern = substr($metaphone, 0, $i);
                $level = lhTextConv::metaphoneSimilarity($t_pattern, $m_pattern);
                if ($level > $match_level) {
                    $match_level = $level;
                    $best_match = (string)$pattern;
                }
            }
        }
        $this->log(__CLASS__.'->'.__FUNCTION__.' have taken '.  (microtime(true)-$microtime). ' seconds', 5);
        return $this->match_return($best_match, $match_level, 'start', 'full');
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
            'bestMatches' => [  // простое тестирование для совместимости. Более подробное дальше
                [
                    "привет", ['fullmatch'], 50, 
                    new lhTest(function($result){
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
                [
                    "Здравствуйте, дорогие товарищи!", "#startmatch", 90,
                    new lhTest(function ($result){
                        $found = false;
                        foreach ($result as $key=>$value) {
                            $found = true;
                            if ($value[1]['name'] != "%GREETING%") {
                                throw new Exception("Ожидалось найти категорию %GREETING%. Нашлось ". print_r($result, true));
                            }
                        }
                        if (!$found) {
                            throw new Exception("Ожидалось найти категорию %GREETING%. Нашлось ". print_r($result, true));
                        }
                    })
                ],
                [ "Хер вам, дорогие товарищи!", "#startmatch", 80, []],
                [ "Петь, привет. У меня сломался принтер", "", 80, []],            
                [
                    "с добрым утром петя", "#startmatch", 80,
                    new lhTest(function ($result){
                        $found = false;
                        foreach ($result as $key=>$value) {
                            $found = true;
                            if ($value[1]['name'] != "%GREETING%") {
                                throw new Exception("Ожидалось найти категорию %GREETING%. Нашлось ". print_r($result, true));
                            }
                        }
                        if (!$found) {
                            throw new Exception("Ожидалось найти категорию %GREETING%. Нашлось ". print_r($result, true));
                        }
                    })
                ],
            ],
            'match_full' => '_test_skip_'  // протестировано в bestMatches 
        ];
    }
}
