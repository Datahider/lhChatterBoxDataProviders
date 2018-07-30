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
        $metaphone = lhTextConv::metaphone($text);
        $aiml = $this->getAiml();
        $tags = $this->splitTags($tags);
        foreach ($aiml->category as $category) {
            if ($this->hasTags($tags, $category)) {
                foreach ($category->pattern as $pattern) {
                    $index = sprintf("%010.6f", lhTextConv::similarity($metaphone, lhTextConv::metaphone($pattern)));
                    if ($index >= $minhitratio) {
                        $result[$index] = [(string)$pattern, (array)$category->template->random->li];
                    }
                }
                
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
        if (is_scalar($tags)) {
            $tags = preg_split("/\s*#/", $tags, -1, PREG_SPLIT_NO_EMPTY);
        }
        return $tags;
    }
    
    public function hasTags($tags, $category) {
        $tags = $this->splitTags($tags);
        $has_tag = true;
        foreach ($tags as $tag) {
            $has_tag = false;
            foreach ($category->tag as $category_tag) {
                if (mb_strtolower($category_tag) == mb_strtolower($tag)) {
                    $has_tag = true;
                    break;
                }
            }
            if (!$has_tag) { break; }
        }
        return $has_tag;
    }
}
