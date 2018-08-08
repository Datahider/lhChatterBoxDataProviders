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
require_once LH_LIB_ROOT . '/lhTextConv/lhTextConv.php';

class lhCSML extends lhAbstractCSML {
    
    public function start($name='start') {
        $this->current = $name;
    }
    
    public function answer($user_answer, $minhitratio=0, $name=null) {
        $name = $this->setCurrent($name);
        // Сначала запустим все валидаторы
        $answer = $this->runValidators($user_answer);
        if (!$answer) {
            $answer = $this->bestAnswer($user_answer, $minhitratio);
        }
        return $answer;
    }
    
    public function block($name=null) {
        $name = $this->setCurrent($name);
        foreach ($this->getCsml()->block as $block) {
            if ((string)$block['name'] == $name) {
                return $block;
            }
        }
        throw new Exception("Не найден блок \"$name\"");
    }
    
    private function setCurrent($name) {
        if ($name !== null) {
            $this->current = $name;
        }
        return $this->current;
    } 
    
    // Возвращает объект xml - блок <answer/> выбранный 
    // по первому сработавшему валидатору
    private function runValidators($text) {
        $block = $this->block();
        foreach ($block->answer as $answer) {
            foreach ($answer->validator as $validator) {
                $validator_class = (string)$validator['name'];
                $v = new $validator_class;
                $result = $v->validate($text);
                $vars = $v->moreInfo();
                if ((string)$validator['var']) {
                    if ((string)$validator) {
                        $result = isset($vars[(string)$validator['var']])? ($vars[(string)$validator['var']] == (string)$validator) : false;
                    } else {
                        $result = isset($vars[(string)$validator['var']]) ? (bool)$vars[(string)$validator['var']] : false;
                    }
                }
                $result = $validator['not'] ? !$result : $result;
                if ($result) {
                    $answer->addChild('validated', json_encode($vars));
                    return $answer;
                }
            }
        }
        return false;
    }
    
    // Возвращает объект xml - блок <answer/> выбранный 
    // по наиболее подходящему ответу или по умолчанию если попадание 
    // в ответы меньше $minhitratio
    private function bestAnswer($text, $minhitratio=0) {
        $block = $this->block();
        $best_match_value = -1;
        $best_match_answer = null;
        $default_answer = false;
        foreach ($block->answer as $answer) {
            foreach ($answer->pattern as $pattern) {
                $match = lhTextConv::metaphoneSimilarity($text, $pattern);
                if ($match > $best_match_value) {
                    $best_match_value = $match;
                    $best_match_answer = $answer;
                }
            }
            if ($answer['default']) {
                $default_answer = $answer;
            }
        }
        if ($best_match_value >= $minhitratio) {
            return $best_match_answer;
        } else {
            return $default_answer;
        }
        
    }
}
