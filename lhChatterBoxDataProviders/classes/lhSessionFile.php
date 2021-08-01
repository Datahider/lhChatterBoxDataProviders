<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhSessionFile
 * 
 * define('LH_LIB_ROOT', './');                     
 * define('LH_SESSION_DIR', '/var/lhsession/');
 * define('LH_SESSION_LOG_SESSION', true);  // для включения логирования установки значений сессии
 * define('LH_SESSION_LOG_DEBUG', true);    // для включения логирования отладки
 *
 *
 * @author Петя Datahider
 */

require_once __DIR__ . '/../abstract/lhAbstractSession.php';

class lhSessionFile extends lhAbstractSession {

    public function __construct($session=null) {
        $this->session_id = $session;
        $this->checkSessionDir();
        
        $this->readSessionData();
    }
    
    public function get($param, $default=null) {
        if (isset($this->session_data[$param])) {
            return $this->session_data[$param];
        } else {
            return $default;
        }
    }
    
    public function set($param, $value) {
        $this->log(self::$facility_session, "Установка параметра сессии $this->session_id \"$param\". Старое значение: ", isset($this->session_data[$param]) ? $this->session_data[$param] : "*** NOT SET ***");
        $this->session_data[$param] = $value;
        $this->writeSessionData();
        $this->log(self::$facility_session, "Установка параметра сессии $this->session_id \"$param\". Новое значение: ", $this->session_data[$param]);
    }

    public function writeLog($facility, $value1, $value2=null, $value3=null, $value4=null, $value5=null) {
        if (!defined('LH_SESSION_LOG_SESSION') && ($facility == self::$facility_session)) return; // не пишем лог сессии если не установлен LH_SESSION_LOG_SESSION
        if (!defined('LH_SESSION_LOG_DEBUG') && ($facility == self::$facility_debug)) return; // не пишем лог отладки если не установлен LH_SESSION_LOG_DEBUG

        if ($facility == 'test') {
            throw new Exception('$facility не может быть равно "test"');
        }
        
        $log_file = LH_SESSION_DIR.$this->session_id.'.'.$facility;
        $date = date(DATE_ISO8601);
        if (is_scalar($value1)) {
            $log_line = "$date: $value1";
        } else {
            $log_line = "$date: ".print_r($value1, true);
        }
        $log_line = $value2 ? "$log_line $value2" : $log_line;
        $log_line = $value3 ? "$log_line $value3" : $log_line;
        $log_line = $value4 ? "$log_line $value4" : $log_line;
        $log_line = $value5 ? "$log_line $value5" : $log_line;
        
        $log_line = preg_replace("/\n/u", '\\n', $log_line);
        
        file_put_contents($log_file, "$log_line\n", FILE_APPEND);
    }
    
    public function readLog($facility='log', $last=20) {
        if ($facility == 'test') {
            throw new Exception('$facility не может быть равно "test"');
        }
        $log_file = LH_SESSION_DIR.$this->session_id.'.'.$facility;
        
        $out = file($log_file, FILE_IGNORE_NEW_LINES);
        if ($out !== false) {
            $out = array_slice($out, -$last);
            return $out;
        } else {
            return false;
        }
    }

    protected function checkSessionDir() {
        if (!defined('LH_SESSION_DIR')) {
            define('LH_SESSION_DIR', '/var/lhsession/');
        }
        $test_file = LH_SESSION_DIR.'write.test';
        $h = fopen($test_file, 'w+');
        if (!$h) {
            throw new Exception('Каталог "'.LH_SESSION_DIR.'" не доступен для записи.');
        }
        fclose($h); unlink($test_file);
    }
    
    protected function readSessionData() {
        if (is_readable(LH_SESSION_DIR.$this->session_id.'.data')) {
            $json = file_get_contents(LH_SESSION_DIR.$this->session_id.'.data');
            $this->session_data = json_decode($json, true);
        } else {
            $this->session_data['session_id'] = $this->session_id;
            $this->writeSessionData();
            $this->log(self::$facility_session, "Инициализация сессии $this->session_id");
        }
        return $this->session_data;
    }
    
    protected function writeSessionData() {
        $result = file_put_contents(LH_SESSION_DIR.$this->session_id.'.data', json_encode($this->session_data));
        if ($result === false) {
            throw new Exception("Не могу записать данные сессии в файл");
        }
    }
    
    public function destroy() {
        unlink(LH_SESSION_DIR.$this->session_id.'.data');
        $this->session_id = null;
        $this->session_data = null;
    }
}
