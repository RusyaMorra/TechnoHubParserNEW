<?php

namespace modules\parser;

use \phpQuery;

class ParserCardsClass {

    //тут храним домен
    public $domen;
    //тут храним url парсинга 
    public $siteURL;
    //тут храним url get параметры
    public $getParameters;
    //тут храним количество страниц пагинации
    public $paginationCount;
    //массив ссылок на категории
    public $categoriesArrayOfLinks;
    //массив названий категории
    public $categoriesArrayOfNames;

 
    //Тут храняться ссылки для прохода во вложености карточек 
    protected $cardsDataLinks = [];
    //Тут храняться ключи и значения собранной информации 
    protected $cardsDataList = [];
    //Тут храним коллекции картинок
    protected $arrListImages = [];


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
        $this->domen = $Parameters['domen'];
        $this->siteURL = $Parameters['siteURL'];
        $this->getParameters = $Parameters['getParameters'];
        $this->paginationCount = $Parameters['paginationCount'];
        $this->categoriesArrayOfNames = $Parameters['categoriesArrayOfNames'];
        $this->categoriesArrayOfLinks = $Parameters['categoriesArrayOfLinks'];
     

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
        // Тут должен быт цикл  для  сборки (нужная страница, категория, пагинация)
       
        for ($j=0; $j < count($this->categoriesArrayOfLinks) ; $j++) {

            for ($i=0; $i < $this->paginationCount + 1; $i++) {
                //echo $this->siteURL. $this->categoriesArrayOfLinks[$j] . $this->getParameters .  $i ; ?><br><?
                
                $this->parserCards($this->ParserSiteRequest($this->siteURL. $this->categoriesArrayOfLinks[$j] . $this->getParameters .  $i));

            }
        }

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
    private function parserCards($result){
       
        if($result == false) {     
            echo "Ошибка CURL: " . curl_error($curl);
            return false;
        }else {
            $pq = phpQuery::newDocument('<meta charset="utf-8">' . $result); 
            
            
            //получаем внутриности каждой карточки
            $this->GetInnerData($this->GetHrefLinks($pq)); // передаем массив данных + эта функция записывает все ссылки глобально в свойства объекта
           
           
        }

    }



    /**
    *  Получаем href атрибуты карточек(то есть ссылки)
    */

    private function GetInnerData($Links){
        
        foreach($Links as $CardDataLink){
            $resultCard = $this->ParserSiteRequest( $this->domen . $CardDataLink);
            $pq = phpQuery::newDocument('<meta charset="utf-8">' . $resultCard); 
            //получаем картинки
            //$this->GetImgSrc($pq);
            
            //сборка данных в один массив
            
            $this->cardsDataList[] = [
                "name" => $pq->find('.product-top__title')->text(),
            // "price"=> intval(preg_replace('/[^0-9]/', '', $pq->find('.total-price')->text())),
            // "oldprice" =>intval(preg_replace('/[^0-9]/', '', $pq->find("#old-price-field")->text())),
            // "currencyId" => 'RUR',
            // "categoryId" => '21',
            // "store" => 'false',
            // "pickup" => 'true',
            // "delivery" => 'true',
            // "vendor" => 'Elektronika',
                "url" => "https://belwood.kz". $CardDataLink,
                "description" => $pq->find('.product-info__text p')->text()
            ];
           

        }
    }





    /**
    *  Получаем href атрибуты карточек(то есть ссылки)
    */

    private function GetHrefLinks($pq){
         // парсим все ссылки товара на страницах 
         $listlinks = $pq->find('.catalog-item .catalog-item__title-container a');
         
         $localLinksForReturn = [];
         // проходимься по элементам и берем у кажной атримут href 
         foreach($listlinks as $link){
            $this->cardsDataLinks[] = pq($link)->attr('href');
            $localLinksForReturn[] = pq($link)->attr('href');
         }
         
         return  $localLinksForReturn;
    }

    /**
    * Получаем ссылки на картинки
    */

    private function GetImgSrc($pq){
       /* получаем изображения */
       $listImages = $pq->find("a.js-varaint-image");

       foreach($listImages as $image) {
            //Тут пишем нужный атрибут либо src либо data-img итд
            $this->$arrListImages[] = pq($image)->attr("data-image");
        }
    }



  


    /**
    *  Запускаем сохранение в разные форматы
    */

    private function saving(){
        $this->jsonSave();
        $this->TXTSave();
        
    }


    /**
    *  Сохранение json
    */
    private function jsonSave(){
        $jsonData = json_encode($this->cardsDataList, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./data/json/json_data.json", $jsonData);

    }

    /**
    *  Сохранение txt
    */
    private function TXTSave(){
        $jsonData = json_encode($this->cardsDataList, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
        file_put_contents("./data/txt/txt_Data.txt", $jsonData);

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

    

    /**
     * Создает CSV файл из переданных в массиве данных.
     *
     * @param array  $create_data   Массив данных из которых нужно созать CSV файл.
     * @param string $file          Путь до файла 'path/to/test.csv'. Если не указать, то просто вернет результат.
     * @param string $col_delimiter Разделитель колонок. Default: `;`.
     * @param string $row_delimiter Разделитель рядов. Default: `\r\n`.
     *
     * @return false|string CSV строку или false, если не удалось создать файл.
     *
     * @version 2
     * 
     * Формат Array
     * $create_data = array(
     *       array(
     *          'Заголовок 1',
     *          'Заголовок 2',
     *           'Заголовок 3',
     *      ),
     *      array(
     *           'строка 2 "столбец 1"',
     *           '4799,01',
     *          'строка 2 "столбец 3"',
     *      ),
     *      array(
     *           '"Ёлочки"',
     *           4900.01,
     *          'красный, зелёный',
     *      )
     * );
     */
    
    private  function kama_create_csv_file( $create_data, $file = null, $col_delimiter = ';', $row_delimiter = "\r\n" ){

        if( ! is_array( $create_data ) ){
            return false;
        }

        if( $file && ! is_dir( dirname( $file ) ) ){
            return false;
        }

        // строка, которая будет записана в csv файл
        $CSV_str = '';

        // перебираем все данные
        foreach( $create_data as $row ){
            $cols = array();

            foreach( $row as $col_val ){
                // строки должны быть в кавычках ""
                // кавычки " внутри строк нужно предварить такой же кавычкой "
                if( $col_val && preg_match('/[",;\r\n]/', $col_val) ){
                    // поправим перенос строки
                    if( $row_delimiter === "\r\n" ){
                        $col_val = str_replace( [ "\r\n", "\r" ], [ '\n', '' ], $col_val );
                    }
                    elseif( $row_delimiter === "\n" ){
                        $col_val = str_replace( [ "\n", "\r\r" ], '\r', $col_val );
                    }

                    $col_val = str_replace( '"', '""', $col_val ); // предваряем "
                    $col_val = '"'. $col_val .'"'; // обрамляем в "
                }

                $cols[] = $col_val; // добавляем колонку в данные
            }

            $CSV_str .= implode( $col_delimiter, $cols ) . $row_delimiter; // добавляем строку в данные
        }

        $CSV_str = rtrim( $CSV_str, $row_delimiter );

        // задаем кодировку windows-1251 для строки
        if( $file ){
            $CSV_str = iconv( "UTF-8", "cp1251",  $CSV_str );

            // создаем csv файл и записываем в него строку
            $done = file_put_contents( $file, $CSV_str );

            return $done ? $CSV_str : false;
        }

        return $CSV_str;

    }


}








/*
// Создаём XML-документ
$dom = new DOMDocument('1.0', 'utf-8');
// Создаём корневой элемент <offers>
$root = $dom->createElement('offers');
$dom->appendChild($root);


foreach($offers as $valueParam) {

	// Создаём узел <offer>
	$offer = $dom->createElement('offer');

	// Добавляем дочерний элемент для <offers>
	$root->appendChild($offer);

	// Устанавливаем атрибут id для узла <offer>
	$offer->setAttribute('id', $valueParam['id']);

	foreach($valueParam["listMainParams"] as $key=>$val) {
		$params = $dom->createElement($key, $val);
		$offer->appendChild($params);
	}

	foreach($valueParam["listImages"] as $image) {
		$params = $dom->createElement("picture", $image);
		$offer->appendChild($params);
	}

	foreach($valueParam["listDopParams"] as $dopParam) {
		$params = $dom->createElement("param", $dopParam["value"]);
		$params->setAttribute('name', $dopParam["name"]);
		$offer->appendChild($params);
	}

}

// Сохраняем полученный XML-документ в файл
$dom->save('offers.xml');

*/