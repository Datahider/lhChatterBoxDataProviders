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
require_once LH_LIB_ROOT . 'lhChatterBoxDataProviders/abstract/lhAbstractAIML.php';

class lhAIML extends lhAbstractAIML {
    
    public function bestMatches($text, $tags=[], $minhitratio=0) {
        $aiml = $this->getAiml();
        $tags = $this->splitTags($tags);
        foreach ($aiml->category as $category) {
            
        }
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
                if ($category_tag == $tag) {
                    $has_tag = true;
                    break;
                }
            }
            if (!$has_tag) { break; }
        }
        return $has_tag;
    }
}
