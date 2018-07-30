<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhAbstractCSML
 *
 * @author user
 */
require_once __DIR__ . '/../interface/lhCSMLInterface.php';

abstract class lhAbstractCSML implements lhCSMLInterface {
    private $csml;
    protected $current;


    public function __construct() {
        $this->csml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><csml/>');
        $this->start();
    }
    
    public function setCsml($xml_object) {
        $this->csml = $xml_object;
    }
    
    public function csmlFromString($xml_string) {
        $this->setCsml(new SimpleXMLElement($xml_string));
    }
    
    public function loadCsml($stream_name) {
        $this->csmlFromString(file_get_contents($stream_name));
    }
    
    public function getCsml() {
        return $this->csml;
    }
    
    public function csmlCheck() {
        foreach ($this->getCsml()->block as $block) {
            $block_names[] = $block['name'];
            foreach ($block->answer as $answer) {
                if (array_search((string)$answer->next, $block_names) === false) {
                    $not_found[] = (string)$answer->next;
                }
            }
        }
        $not_found = array_filter($not_found, function ($value) use( $block_names ) {
            return array_search($value, $block_names) === false ? true : false;
        });
        return $not_found;
    }
}
