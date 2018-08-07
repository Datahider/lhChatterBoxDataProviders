<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * lhChatProxyInterface - реализация проксирования реплики пользователя 
 * чат бота оператору
 *
 * @author Петя Datahider
 */
interface lhChatProxyInterface {
    
    /**
     * public function request($text) - передача запроса оператору
     * 
     * Функция должна вызываться из чат-бота, для передачи того, что написал
     * пользователь оператору чат-бота если сам чат-бот не в состоянии ответить
     * пользователю
     * 
     * @param string $text Текст напечатанный пользователем
     * @return bool true в случае успешной передачи, false в случае ошибки
     */
    public function request($text);
    
    /**
     * public function reply($answer) - ответ оператора пользователю 
     * через чат-бот
     * 
     * Функция вызывается из обработчика ответа оператора (вебхук или другой
     * обработчик, который обрабатывает ответную реакцию оператора) и передает
     * ответ оператора пользователю чат-бота через вызов API чат-бота
     * 
     * @param array $answer массив ответа: [ 'текст', ['hint1', 'hint2'...] ]
     */
    public function reply($answer);
    
}
