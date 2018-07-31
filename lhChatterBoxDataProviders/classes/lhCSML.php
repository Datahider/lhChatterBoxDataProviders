<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhCSML
 *
 * @author user
 */
require_once __DIR__ . '/../abstract/lhAbstractCSML.php';

class lhCSML extends lhAbstractCSML {
    
    public function start($name='start') {
        $this->current = $name;
    }
    
    public function answer($user_answer, $minhitratio=0, $name=null) {
        $name = $this->setCurrent($name);
        // TODO - перед поиском наиболее подходящего ответа подключить валидацию
        $answer = $this->bestAnswer($user_answer, $minhitratio);
        return $answer;
    }
    
    public function block($name=null) {
        $name = $this->setCurrent($name);
        foreach ($this->getCsml()->block as $block) {
            if ((string)$block['name'] == $name) {
                return $block;
            }
        }
    }
    
    private function setCurrent($name) {
        if ($name !== null) {
            $this->current = $name;
        }
        return $this->current;
    } 
        
    private function bestAnswer($text, $minhitratio=0) {
        $block = $this->block();
        $best_match_value = -1;
        $best_match_answer = null;
        foreach ($block->answer as $answer) {
            foreach ($answer->pattern as $pattern) {
                $match = lhTextConv::metaphoneSimilarity($text, $pattern);
                if ($match > $best_match_value) {
                    $best_match_value = $match;
                    $best_match_answer = $answer;
                }
            }
        }
        if ($best_match_value >= $minhitratio) {
            return $best_match_answer;
        } else {
            return false;
        }
        
    }
}
