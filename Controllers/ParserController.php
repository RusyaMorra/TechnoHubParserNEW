<?php

//*************************************************************************
//* namespaces
//*************************************************************************
namespace Controllers;



use Models\ParserModel;
use modules\parser\ParserCardsClass;

class ParserController {
 
    public $parserModelClass= null;

    public function __construct() {
        $this->Cors();
        $this->StartParsing();
        $this->loadModel();
        //$this->ParserModelTasks();
        
    }

        
    //*************************************************************************
    //* Подключены заголовки для Cors
    //*************************************************************************

    public function StartParsing(){
        $parserClass = new ParserCardsClass('https://belwood.kz/catalog/mezhkomnatnye_dveri');
    
    }
    
    //*************************************************************************
    //* Подключены заголовки для Cors
    //*************************************************************************

    public function Cors(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials: true');
        header('Content-type: json/application');
        header('Content-Type: text/html; charset=utf-8');
    
    }

    //*************************************************************************
    //* Подгрузка моделей
    //*************************************************************************

    
    public function loadModel() {
        $this->parserModelClass = new ParserModel();
    }
 
    //*************************************************************************
    //* тут выполняються определенные команды к модели
    //*************************************************************************
     
    public function ParserModelTasks() {
        $this->parserModelClass->saveCardsInDb();
    }

}