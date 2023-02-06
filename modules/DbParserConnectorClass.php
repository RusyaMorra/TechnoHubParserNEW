<?php

namespace modules;

use Krugozor\Database\Mysql;

class DbParserConnectorClass {
 
  static private $_instance = null;
  static public $db = null;

  static public function createConnection(){
    self::$db = Mysql::create("localhost", "root", "")->setDatabaseName("parser")->setCharset("utf8");
    
  }

    /**
     *  singleton
     */

  static public function getInstance(){
    if(self::$_instance == null){
      return self::$_instance = new self;
        
    }

    return self::$_instance;
  }
 
  /**
  *  Получаем лист данных из json и делаем insert по ключам 
  */
  public function insertParserList($cards){
    print_r($cards);
    self::$db->query('INSERT INTO `cards` SET ?As', $cards);
  }


}
