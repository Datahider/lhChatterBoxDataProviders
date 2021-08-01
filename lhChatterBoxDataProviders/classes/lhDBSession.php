<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhBillingSession
 *
 * @author Peter Datahider
 * 
 * Данные сессии хранятся в таблице:
 * - ($PREFIX).'sessiondata'
 *  Поля
 *      id - varchar(40) - уникальный идентификатор сесии
 *      data - text
 * SQL для создания таблицы:
 *      CREATE TABLE sessiondata` ( `id` VARCHAR(40) NOT NULL, 
 *      `data` TEXT NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE = InnoDB;
 *  
 */
class lhDBSession extends lhAbstractSession {
    
    protected $dbconn;
    protected $session_table;
    protected $read;

    public function __construct($session = NULL, $dbconn=NULL, $prefix='') {
        $this->session_id = $session;
        $this->session_table = $prefix.'sessiondata';
        $this->dbconn = $dbconn;
        $this->read = false;
    }
    
    public function dbConnect($host, $db, $user, $password, $prefix='') {
        $this->dbconn = new PDO('mysql:host='.$host.';dbname='.$db.';charset=UTF8', $user, $password);
        $this->session_table = $prefix.'sessiondata';
    }

    protected function readSessionData() {
        $sql = "SELECT * FROM $this->session_table WHERE id=:id LIMIT 1";
        $st = $this->dbconn->prepare($sql);

        if (!$st->execute([':id' => $this->session_id])) { 
            throw new Exception("Ошибка выполнения запроса: $sql:\n" . print_r($st->errorInfo(), true));
        }

        $row = $st->fetch(PDO::FETCH_OBJ);
        if (empty($row)) {
            $this->session_data = new stdClass();
        } else {
            $this->session_data = json_decode($row->data);
        }
        $this->read = true;
        return $this->session_data;
    }
    
    protected function writeSessionData() {
        $data = json_encode($this->session_data);
        $sql = "INSERT INTO $this->session_table (id, data) VALUES(:id, :data) "
                    . "ON DUPLICATE KEY UPDATE data=VALUES(data)";
        $st = $this->dbconn->prepare($sql);

        if (!$st->execute([ ':id' => $this->session_id, ':data' => $data ])) {
            throw new Exception("Ошибка выполнения запроса: $sql:\n" . print_r($st->errorInfo(), true));
        }
    }
    
    public function set($param, $value) {
        if ($this->read == false) {
            $this->readSessionData();
        }

        $this->session_data->$param = $value;
        $this->writeSessionData();
    }
   
    public function writeLog($facility, $value1, $value2=null, $value3=null, $value4=null, $value5=null) {
        // do nothing for now
    }

    public function destroy() {
        $sql = "DELETE FROM $this->session_table WHERE id=:id LIMIT 1";
        $st = $this->dbconn->prepare($sql);

        if (!$st->execute([':id' => $this->session_id])) { 
            throw new Exception("Ошибка выполнения запроса: $sql:\n" . print_r($st->errorInfo(), true));
        }
        
        $this->read = false;
        $this->session_data = new stdClass();
    }

    public function get($param, $default = null) {
        if ($this->read == false) {
            $this->readSessionData();
        }
        
        if (isset($this->session_data->$param)) {
            return $this->session_data->$param;
        } else {
            return $default;
        }
    }

    public function readLog($facility = 'log', $last = 20) {
        return "readLog isn't implemented yet";
    }

    protected function _test_data() {
        return [
            'dbConnect' => [
                ['localhost', 'test', TEST_DB_USER, TEST_DB_PASSWORD, 'test_', null]
            ],
            'readSessionData' => '_test_skip_',
            'writeSessionData' => '_test_skip_',
            'set' => [
                ['test_var', 18, null],
                ['test_var_2', 'a_test_string', null],
                ['test_var', 108, null]
            ],
            'get' => [
                ['test_var', 15, 108],
                ['test_var_2', 'a_test_string'],
                ['test_var_3', 999, 999]
            ],
            'writeLog' => '_test_skip_',
            'readLog' => '_test_skip_',
            'destroy' => '_test_skip_'
        ];
    }
}
