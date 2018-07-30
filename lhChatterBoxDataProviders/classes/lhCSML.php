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
require_once LH_LIB_ROOT . 'lhChatterBoxDataProviders/abstract/lhAbstractCSML.php';

class lhCSML extends lhAbstractCSML {
    
    public function start($name='start') {
        $this->current = $name;
    }
    
    public function next($user_answer, $minhitratio=0, $name=null) {
        $name = setCurrent($name);
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
          
}
