<?php

namespace modules\parser;

use \phpQuery;

class ParserCategoryClass {

    public $siteURLWithCatNames;
    public $siteURLWithCatLinks;
    public $parsingType;

 
    //Тут  храняться  все категории
    protected $CategoryNameList = [];
    //Тут  храняться  все полные ссылки на категорию
    protected $catDataFullUrlLinks = [];
    //Тут  храняться  все  юрлы именно ссылок
    protected $catUrlLinks = [];
   


   /**
    *  В инпут получаем url спарсеной  страницы 
    */
    public function __construct(array $Parameters) {
        
        $this->writeInObjectProperties($Parameters);
        $this->errorDetector();
        $this->parserStarter();
    
    }


    /**
    *  Записываем в свойста обьекта 
    */
    public function writeInObjectProperties(array $Parameters){
       $this->siteURLWithCatNames = $Parameters['siteURLWithCatNames'];
       $this->siteURLWithCatLinks = $Parameters['siteURLWithCatLinks'];
       $this->parsingType = $Parameters['ParsingType'];
       
    }





    /**
    *  Детектим ошибки
    */
    public function errorDetector(){
        setlocale(LC_ALL, 'ru_RU.UTF-8');
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

    }

    /**
    *  Входная точка 
    */

    private function parserStarter() {
        $link = null;

        if($this->parsingType == 'links'){
            $link =  $this->siteURLWithCatLinks;
        }elseif($this->parsingType == 'names'){
            $link =  $this->siteURLWithCatNames;
        }
        
       
       $this->parsePageWithCategoryList($this->ParserSiteRequest($link));


      
       //сохраняем
        $this->saving();
    }


    /**
    *  Тут мы делаем запрос и получаем страницу
    */
    private function ParserSiteRequest(string $url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        /*$info = curl_getinfo($curl);
        print_r($info);*/
        $result = curl_exec($curl);
        return $result;
    }
    

    /**
    * Получаем через инпут спарсенную страницу и вытаскиеваем данные с нужных полей и после 
    */
    private function parsePageWithCategoryList($result){
       
        if($result == false) {     
            echo "Ошибка CURL: " . curl_error($curl);
            return false;
        }else {
            $pq = phpQuery::newDocument('<meta charset="utf-8">' . $result);

            //Проверяем тип
            if($this->parsingType == 'links'){
                //используем если нужно получить название ссылки
                $this->getCatLinks($pq);
            }elseif($this->parsingType == 'names'){
                //используем если нужно получить название категории
                $this->getCatNames($pq);

            }

            
        }

    }





    /**
    *  Получаем href атрибуты карточек(то есть ссылки)
    */

    private function getCatLinks($pq){

         // парсим название всех категорий
         $Categorieslinks = $pq->find('.catalog-categories__item a');

        
       
        // проходимься по элементам и берем у кажной атримут href 
        foreach($Categorieslinks as $link){
            $this->catDataFullUrlLinks[] = pq($link)->attr('href');
            
        }
         
       
    }
    /**
    *  Получаем тект(название) категорий
    */

    private function getCatNames($pq){

         // парсим название всех категорий
         $CategoriesNames = $pq->find('.filters__title span');

        
       
         foreach($CategoriesNames as $cat){
            $this->CategoryNameList[] = $cat->nodeValue;
           
         }
         
       
    }

    




    /**
    *  Запускаем сохранение в разные форматы
    */

    private function saving(){

        if($this->parsingType == 'links'){
            $this->jsonSaveLinks();
            $this->TXTSaveLinks();
        }elseif($this->parsingType == 'names'){
            $this->jsonSaveNames();
            $this->TXTSaveNames();
        }

       
        
    }


    /**
    *  Сохранение json для ссылок
    */
    private function jsonSaveLinks(){
        $jsonData = json_encode($this->catDataFullUrlLinks, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./categoriesData/links/json/cat_data_links.json", $jsonData);

    }

    /**
    *  Сохранение txt для ссылок
    */
    private function TXTSaveLinks(){
        $jsonData = json_encode($this->catDataFullUrlLinks, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./categoriesData/links/txt/cat_data_links.txt", $jsonData);

    }


    /**
    *  Сохранение json для имен
    */
    private function jsonSaveNames(){
        $jsonData = json_encode($this->CategoryNameList, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./categoriesData/names/json/cat_data_names.json", $jsonData);

    }

    /**
    *  Сохранение txt для имен
    */
    private function TXTSaveNames(){
        $jsonData = json_encode($this->CategoryNameList, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./categoriesData/names/txt/cat_data_names.txt", $jsonData);

    }

    /**
    *  Для нумирации с названием
    */

    private function preFormat($arratOfValues, $numTitle='Карточка'){
        $arrayLength = count($arratOfValues);
        $numName = $numTitle;
        $resArray = [];
        for($i = 0 ; $i < $arrayLength; $i++ ){
            $resArray[] =  $numName . $i;
        }
        return  $resArray;
    }

    



}






