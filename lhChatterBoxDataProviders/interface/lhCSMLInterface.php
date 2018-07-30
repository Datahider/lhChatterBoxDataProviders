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
    public function next($user_answer, $minhitratio=0, $name=null); // Возвращает имя следующего блока скрипта в зависимости от ответа пользователя
    public function block($name=null); // Возвращает блок в виде объекта xml
    
}
