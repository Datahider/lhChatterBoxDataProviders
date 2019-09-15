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


    public function __construct($csml=null) {
        if ($csml === null) {
            // Если не передано значение - инициализируем пустым
            $this->setCsml(new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><aiml/>'));
        } else {
            // Если передано - сначала думаем, что это XML
            try {
                $this->csmlFromString($csml);
            } catch (Exception $exc) {
                // Если объект из строки не создался - похоже это имя файла;
                $this->loadCsml($csml);
            }
        }
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
        $not_found = [];
        foreach ($this->getCsml()->block as $block) {
            $block_names[] = (string)$block['name'];
            foreach ($block->answer as $answer) {
                foreach ($answer->next as $next) {
                    if (array_search((string)$next, $block_names) === false) {
                        if (array_search((string)$next, $not_found) === false) {
                            $not_found[] = (string)$next;
                        }
                    }
                }
            }
        }
        $not_found = array_filter($not_found, function ($value) use( $block_names ) {
            return array_search($value, $block_names) === false ? true : false;
        });
        return $not_found;
    }
}
