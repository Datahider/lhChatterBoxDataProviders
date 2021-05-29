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

    public function __construct($session, $dbconn, $prefix='') {
        $this->session_id = $session;
        $this->session_table = $prefix.'sessiondata';
        $this->dbconn = $dbconn;
        $this->readSessionData();
    }
    
    protected function readSessionData() {
        $sql = "SELECT * FROM $this->session_table WHERE id=:id LIMIT 1";
        $st = $this->dbconn->prepare($sql);
        $st->execute([':id' => $this->session_id]);
        $row = $st->fetch(PDO::FETCH_OBJ);
        if (empty($row)) {
            $this->session_data = new stdClass();
        } else {
            $this->session_data = json_decode($row->data);
        }
        return $this->session_data;
    }
    
    protected function writeSessioData() {
        $data = json_encode($this->session_data);
        $sql = "INSERT INTO $this->session_table (id, data) VALUES(:id, :data) '
                    . 'ON DUPLICATE KEY UPDATE data=VALUES(data)";
        $st = $this->dbconn->prepare($sql);
        $st->execute([
            ':id' => $this->session_id,
            ':data' => $data
        ]);
    }
    
    public function set($param, $value) {
        $this->session_data->$param = $value;
        $this->writeSessioData();
    }
   
    public function log($facility, $value1, $value2=null, $value3=null, $value4=null, $value5=null) {
        // do nothing for now
    }
}
