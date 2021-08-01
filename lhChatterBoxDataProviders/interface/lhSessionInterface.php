<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhSessionInterface
 * Получение и сохранение данных сессии
 *
 * @author Петя Datahider
 */
interface lhSessionInterface {

    public function __construct($session=null); // конструктор получающий id сессии
    public function get($param, $default=null); // Получение параметра сессии или $default если не найден
    public function set($param, $value); // Установка параметра сессии
    public function writeLog($facility, $value1, $value2=null, $value3=null, $value4=null, $value5=null); // Запись в лог
    public function readLog($facility='log', $last=20);
    
}
