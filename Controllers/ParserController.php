<?php

//*************************************************************************
//* namespaces
//*************************************************************************
namespace Controllers;



use Models\ParserModel;
use modules\parser\ParserCardsClass;
use modules\parser\ParserCategoryClass;

class ParserController {
 
    /**
    * Гет параметры нужны для  навигации по погинации что бы через итерацию  собрать все записи на всех страницах
    */
    private $metaArray = [
        'domen' => 'https://belwood.kz',
        'siteURL' => 'https://belwood.kz',
        'getParameters' => '?PAGEN_1=', // в конец ставим числа из итерации
        'paginationCount' => 0, // число страниц в пагинации что бы задать  итерацию
        'categoriesArrayOfNames' => [],
        'categoriesArrayOfLinks' => [],
    ];
    private $siteURLWithCategoryInfo = [
       'siteURLWithCatNames' => 'https://belwood.kz/catalog/mezhkomnatnye_dveri',//для страницы  с именами  на категории
       'siteURLWithCatLinks' => 'https://belwood.kz/catalog', //для страницы  с ссылками  на категории
       'ParsingType' => 'links',//  может быть names или links
       
    ];

    public $parserModelClass= null;

    

    public function __construct() {
        $this->Cors(); // Подключаем cors
        // $this->startCategoriesParsing(); // парсим инфу по катеориям пока доступно ссылки и названия
        $this->pullingOutCatArray(); // вытягиваем данные из json в массив  $metaArray
         $this->startCardsParsing(); // парсим карточки сайта
        $this->loadModel(); // подгрузка модели


        //для проверки  инфы по категориям
        // print_r($this->metaArray['categoriesArrayOfNames']);
        // print_r($this->metaArray['categoriesArrayOfLinks']);

        
        
        //$this->ParserModelTasks(); // это  нужно если работаем с базой данных
        
    }

        
    //*************************************************************************
    //* Запускаем парсинг карточек
    //*************************************************************************

    public function startCardsParsing(){

        //что бы запустить пасинг карточек
        $parserClass = new ParserCardsClass($this->metaArray);

       
    
    }
    //*************************************************************************
    //* Запускаем парсинг карточек
    //*************************************************************************

    public function startCategoriesParsing(){

      //что бы получить категории
        $parserClass = new ParserCategoryClass($this->siteURLWithCategoryInfo);
    
    }
    
    //*************************************************************************
    //*  преобразуем json в обьект и записываем в массив $metaArray[categoriesArray]
    //*************************************************************************

    public function pullingOutCatArray(){

        if($this->siteURLWithCategoryInfo['ParsingType'] == 'links'){

            $json = file_get_contents("./categoriesData/links/json/cat_data_links.json");
            $catLinkData = json_decode($json, true);
            $this->metaArray['categoriesArrayOfLinks'] =  $catLinkData;

        }elseif($this->siteURLWithCategoryInfo['ParsingType'] == 'names'){ 
            $json = file_get_contents("./categoriesData/names/json/cat_data_names.json");
            $catNameData = json_decode($json, true);
            $this->metaArray['categoriesArrayOfNames'] = $catNameData;
        }

        
        
    
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