<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhAimlInterface
 *
 * @author Петя Datahider
 */
interface lhAimlInterface {

    // bestMatches($text, $tags='', $minhitrate=0)
    // 
    // $text - текст паттерна, который мы ищем 
    // $tags - дополнительный фильтр по контексту. Значения элемента category->tag
    //          которые должны присутствовать в выбранных категориях
    //          по умолчанию не фильтруется
    // $minhitrate - минимально допустимое значение lhTextConv::metaphoneSimilarity
    //          для выбранных значений. По умолчанию не фильтруется 
    // 
    // Возвращает отсортированный по убыванию lhTextConv::metaphoneSimilarity массив
    // Индекс массива - значение lhTextConv::metaphoneSimilarity
    //          Каждый элемент представляет собой массив, первый элемент
    // которого - это совпавший pattern
    // Второй элемент - это объект <category/> в котором найден совпавший паттерн
    //
    public function bestMatches($text, $tags='', $minhitrate=0);
}

 