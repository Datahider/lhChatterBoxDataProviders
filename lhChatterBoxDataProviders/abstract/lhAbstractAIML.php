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

require_once LH_LIB_ROOT . 'lhChatterBoxDataProviders/interface/lhAimlInterface.php';

abstract class lhAbstractAIML implements lhAimlInterface {
    private $aiml;
    
    public function __construct() {
        $this->aiml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><aiml/>');
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
