<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhAbstractSession
 *
 * @author Петя Datahider
 */

require_once __DIR__ . '/../interface/lhSessionInterface.php';

abstract class lhAbstractSession  extends lhSelfTestingClass implements lhSessionInterface {
    
    protected $session_id;
    protected $session_data;

    public static $facility_log = 'log';
    public static $facility_chat = 'chat';
    public static $facility_error = 'error';
    public static $facility_session = 'session';
    public static $facility_debug = 'debug';
    
    abstract public function destroy();
}
