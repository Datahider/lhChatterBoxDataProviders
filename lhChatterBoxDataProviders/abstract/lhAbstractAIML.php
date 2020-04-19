<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhAbstractAIML
 *
 * @author user
 */

require_once __DIR__ . '/../interface/lhAimlInterface.php';

abstract class lhAbstractAIML extends lhSelfTestingClass implements lhAimlInterface {
    private $aiml;
    
    public function __construct($aiml=null) {
        if ($aiml === null) {
            // Если не передано значение - инициализируем пустым
            $this->setAiml(new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><aiml/>'));
        } else {
            if (is_readable($aiml)) {
                $this->loadAiml($aiml);
            } else {
                $this->csmlFromString($aiml);
            }
        }
    }
    
    public function setAiml($xml_object) {
        $this->aiml = $xml_object;
    }
    
    public function aimlFromString($xml_string) {
        $this->setAiml(new SimpleXMLElement($xml_string));
    }
    
    public function loadAiml($stream_name) {
        $this->aimlFromString(file_get_contents($stream_name));
    }
    
    public function getAiml() {
        return $this->aiml;
    }
}
