<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhCSMLInterface
 *
 * @author user
 */
interface lhCSMLInterface {
    
    public function start($name='start');
    public function answer($user_answer, $minhitratio=0, $name=null); // Возвращает ответ из скрипта, который наиболее подходит под ответ пользователя
    public function block($name=null); // Возвращает блок в виде объекта xml
    
}
