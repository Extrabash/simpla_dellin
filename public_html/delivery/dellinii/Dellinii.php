<?php
/**
 * Created by PhpStorm.
 * User: ehrapov
 * Date: 15.10.2018
 * Time: 11:59
 */

require_once('api/Simpla.php');

class DellindeliveryApicore extends Simpla
{
    /**
     * Ключ АПИ
     * @var string
     */
    protected static $api_key;

    /**
     * Адреса методов API (ключ - название метода для вызова)
     * @var array
     */
    protected static $apiUrls = array(
        'calculator' => 'https://api.dellin.ru/v1/public/calculator.json',
        'citySearch' => 'https://www.dellin.ru/api/cities/search.json',
        'login' => 'https://api.dellin.ru/v1/customers/login.json',
        'request' => 'https://api.dellin.ru/v2/request',
        'tracker' => 'https://api.dellin.ru/v2/public/tracker.json',
        'produceDate' => 'https://api.dellin.ru/v1/public/produce_date.json',
        'requestTerminal' => 'https://api.dellin.ru/v1/public/request_terminals.json',
        'terminals' => 'https://api.dellin.ru/v3/public/terminals.json',
        'requestCounteragents' => 'https://api.dellin.ru/v1/customers/counteragents.json',
        'streetKladr' => 'https://api.dellin.ru/v1/public/kladr_street.json',
        'cityInfo' => 'https://api.dellin.ru/v2/public/kladr.json',
        'opfList' => 'https://api.dellin.ru/v1/public/opf_list.json',
        'countriesList' => 'https://api.dellin.ru/v1/public/countries.json',
        'terminalsList' => 'https://api.dellin.ru/v3/public/terminals.json',
        'cities' => 'https://api.dellin.ru/v1/public/cities.json',
    );

    /**
     * Id типов упаковки (для калькулятора)
     * @var array
     */
    public static $packing_types_id = array(
        'box' => '0x951783203a254a05473c43733c20fe72',
        'hard' => '0x838FC70BAEB49B564426B45B1D216C15',
        'additional' => '0x9A7F11408F4957D7494570820FCF4549',
        'bubble' => '0xA8B42AC5EC921A4D43C0B702C3F1C109',
        'bag' => '0xAD22189D098FB9B84EEC0043196370D6',
        'pallet' => '0xBAA65B894F477A964D70A4D97EC280BE',
        'car_glass' => '0x9dd8901b0ecef10c11e8ed001199bf6f',
        'car_parts' => '0x9dd8901b0ecef10c11e8ed001199bf70',
        'complex_pallet' =>'0x9dd8901b0ecef10c11e8ed001199bf71',
        'complex_hard' => '0x9dd8901b0ecef10c11e8ed001199bf6e',
    );

    /**
     * Id типов упаковки (для отправки заявок)
     * @var array
     */
    public static $request_packages_id = array(
        'box'=>'0x82750921BC8128924D74F982DD961379',
        'hard' => '0xA6A7BD2BF950E67F4B2CF7CC3A97C111',
        'additional' => '0xAE2EEA993230333043E719D4965D5D31',
        'bubble' => '0xB5FF5BC18E642C354556B93D7FBCDE2F',
        'bag' => '0x947845D9BDC69EFA49630D8C080C4FBE',
        'pallet' => '0xA0A820F33B2F93FE44C8058B65C77D0F',
        'car_glass' => '0xad97901b0ecef0f211e889fcf4624fed',
        'car_parts' => '0xad97901b0ecef0f211e889fcf4624fea',
        'complex_pallet' => '0xad97901b0ecef0f211e889fcf4624feb',
        'complex_hard' => '0xad97901b0ecef0f211e889fcf4624fec',
    );

    /**
     * Отправка запросов API, не требующих дополнительной обработки в ядре
     * @param $functionName название метода
     * @param $data параметры вызова
     * @param string $requestType тип запроса(по-умолчанию json, остальные будут реализованы в будущих версиях)
     * @return mixed
     */
    public static function sendApiRequest($functionName,$data,$requestType='json'){
        if($requestType == 'json'){
            $data = json_encode($data);
        }
        $url = self::$apiUrls[$functionName];
        $response = json_decode(self::sendCurl($url, $data));
        return $response;
    }

    /**
     * Отправка curl запроса в АПИ
     * @param $url URL адрес запроса
     * @param $postData данные
     * @param string $method метод запроса (post по умолчанию)
     * @param string $type тип данных (json по умолчанию)
     * @return mixed резуль
     */
    protected static function sendCurl($url,$postData,$method="POST",$type="json"){
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            if($type){
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/".$type.";"));
            }
            if($postData){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            }
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_ENCODING ,"");
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        }
    }

    /**
     * Получение статуса заказа
     * Передаем Id заказа(например, накладная, но не обязательно она, api всё равно найдет)
     * @param $docId
     * @return bool|mixed
     */
    public static function GetStatus($params){
        if(isset($params['docid'])){
            $result = self::sendApiRequest('tracker',$params);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * Поиск информации о городе через api деловых линий.
     * @param $query строка запроса (часть названия города)
     * @return mixed
     */
    public static function SearchCity($query)
    {
        // Формируем строку запроса и отправляем.

        $query = str_replace('ё','e',$query);
        $query = str_replace(['г.','город','гор.'],'',$query);
        $response = self::sendCurl(self::$apiUrls['citySearch']."?q=".urlencode($query),false,"GET");
        If(json_decode($response) == false && !empty(json_decode($response))){
            $response = gzdecode($response);
        }

        return json_decode($response);
    }

    /**
     * Поиск информации об улице через api деловых линий.
     * @param $arParams параметры метода
     * @return array|mixed
     */

    public static function  GetStreetKladr($arParams){
        $arParams['query'] = str_replace(['г.','город','гор.'],'',$arParams['street']);
        $arParams['query'] = str_replace('ё','е',$arParams['query']);
        $cities = self::sendApiRequest('cityInfo',$arParams);
        $cityID = $cities->cities[0]->cityID;
        if($cityID == NULL){
            $result = array('STATUS' => 'ERROR', 'BODY' => 'Не указан город');
        }else{
            $data = array(
                "appkey"    => $arParams['appkey'],
                "cityID"    => $cityID,
                "street"    => $arParams['street'],
                "limit"     => $arParams['limit']
            );
            if(isset($arParams['sessionID'])){
                $data["sessionID"] = $arParams['sessionID'];
            }
            $response = json_decode(self::sendCurl(self::$apiUrls['streetKladr'], json_encode($data,JSON_UNESCAPED_UNICODE)),true);
            if($response == null){
                $result = array('STATUS' => 'ERROR', 'BODY' => 'Нет подключения к API');
            }elseIf(isset($response['errors'])){
                $result = array('STATUS' => 'ERROR', 'BODY' => "Ошибка апи: ".$response['errors']);
            }else{
                $result = $response;
            }
        }
        return $result;
    }

    /**
     * Поиск населенного пункта по региону и названию.
     * @param array $city_list массив городов
     * @param string $param_city_name название города
     * @param string $param_region_name название региона
     * @return mixed
     */
    public static function SelectCityByRegion($city_list, $param_city_name, $param_region_name = '')
    {
        $dl_city = $city_list[0];

        $param_region_name = iconv('utf-8', 'cp1251', $param_region_name);
        $short_region_name = str_replace('область', 'обл.', $param_region_name);

        $param_region_name = iconv('cp1251', 'utf-8', $param_region_name);
        $short_region_name = iconv('cp1251', 'utf-8', $short_region_name);
        foreach ($city_list as $item) {
            $item_city_name = (string)$item->city;
            $item_region_name = (string)$item->regionString;

            if (
                $item_city_name  == $param_city_name && (
                    $item_region_name == $short_region_name ||
                    $item_region_name == $param_region_name
                )
            ) {
                $dl_city = $item;
                break;
            }
        }

        return $dl_city;
    }

    /**
     * Конвертирование ММ в М
     * @param $value значение в мм
     * @return float|int
     */
    public static function convertMMtoM($value){
        $result = $value/1000;
        return $result;
    }

    /**
     * Конвертирование кубических миллиметров в кубические метры.
     * @param float $param_goods_volume_in_mm значение в кубических миллиметрах
     * @return bool|float|int
     */
    public static function ConvertVolumeFromCubicMillimeterToCubicMeter($param_goods_volume_in_mm)
    {
        $volume_in_meter = $param_goods_volume_in_mm / (1000 * 1000 * 1000);

        return $volume_in_meter > 0 ? $volume_in_meter : 0.01;
    }

    /**
     * Определение негаборитного груза, по его размерам.
     * @param int $param_length длина груза
     * @param int $param_width ширина груза
     * @param int $param_height высота груза
     * @return bool
     */
    public static function IsOversized($param_length, $param_width, $param_height)
    {
        return $param_length >= 3000 || $param_width >= 3000 || $param_height >= 3000;
    }

    /**
     * Проверка на перегруз
     * @param float $weight вес груза
     * @return bool
     */
    public static function IsOversizedWeight($weight)
    {
        return $weight > 100;
    }

    /**
     * Определение негаборитного груза, по его объему.
     * @param int $bx_volume объем груза
     * @return bool
     */
    public static function IsOversizedVolume($param_volume)
    {
        return $param_volume >= 27000000000;
    }

    /**
     * Получение массива объемов груза
     * @param int $sized_volume объем груза
     * @param null|int $oversized_volume объем негабаритного груза
     * @return array
     */
    public static function GetVolumeArray($sized_volume, $oversized_volume = null)
    {
        $arVolume = array();

        $arVolume['sized'] = $sized_volume;

        if (!is_null($oversized_volume)) {
            $arVolume['oversized'] = $oversized_volume;
        }
        return $arVolume;
    }

    /**
     * Получение массива объемов груза с проверкой негабаритности его размеров.
     * @param array $param_size_list
     * @param int $sized_volume
     * @param null|int $oversized_volume
     * @return array
     */
    public static function GetVolumeArrayBySize($param_size_list, $sized_volume, $oversized_volume = null)
    {
        if (self::IsOversized($param_size_list['length'], $param_size_list['width'], $param_size_list['height'])) {
            return self::GetVolumeArray($sized_volume, $oversized_volume);
        }

        return self::GetVolumeArray($sized_volume);
    }

    /**
     * Получение массив объемов груза с проверкой негабаритности его объема.
     * @param $param_volume
     * @param $sized_volume
     * @param null $oversized_volume
     * @return array
     */
    public static function GetVolumeArrayByVolume($param_volume, $sized_volume, $oversized_volume = null)
    {
        if (self::IsOversizedVolume($param_volume)) {
            return self::GetVolumeArray($sized_volume, $oversized_volume);
        }

        return self::GetVolumeArray($sized_volume);
    }

    /**
     * Получение списка организационно правовых форм
     * @param array $apiKey ключ АПИ
     * @return array
     */
    public static function GetOpfList($apiKey){
        $result = json_decode(self::sendCurl(self::$apiUrls['opfList'],json_encode(array('appkey'=>$apiKey))));
        if(isset($result->url)){
            $url = $result->url;
            $opfString = self::sendCurl($url,array(),"GET","string");
            $rows = explode("\n",$opfString);
            $arOpfList = array();
            $keys = str_getcsv($rows[0]);
            foreach($rows as $num=>$row){
                if($num != 0){
                    $arValues = str_getcsv($row);
                    $arRow = array();
                    if($arValues[0]){
                        foreach($arValues as $index=>$value){
                            $arRow[$keys[$index]] = $value;
                        }
                        $arOpfList['list'][$arRow['uid']] = $arRow;
                    }

                }
            }
            return $arOpfList;
        }

    }

    /**
     * Получение списка терминалов (данных одного терминала, если есть ID)
     * @param array $apiKey Ключ API
     * @param bool|string $cityKladr Кладр города
     * @param bool|int $terminalId ID терминала
     * @return array
     */
    public static function GetTerminals($apiKey,$cityKladr=false,$terminalId=false){
        $result = json_decode(self::sendCurl(self::$apiUrls['terminalsList'],json_encode(array('appkey'=>$apiKey))));
        $url = $result->url;
        $hash = $result->hash;
        $terminalString = self::sendCurl($url,array(),"GET","string");
        $arCities = json_decode($terminalString)->city;
        if(!$cityKladr){ //если функция вызвана без параметров - отдаем все терминалы по всем городам
            $arTerminalsByCities = array();
            foreach($arCities as $city){
                $terminalsObs = $city->terminals->terminal;
                $arTerminals = array();
                foreach($terminalsObs as $key=>$terminalOb){
                    $arTerminals[$terminalOb->id] = $terminalOb;
                }
                $cityData = array(
                    'cityName' => $city->name,
                    'cityID' => $city->cityID,
                    'terminals' => $arTerminals
                );
                $arTerminalsByCities[$city->code] = $cityData;
            }
            $arTerminalsList = $arTerminalsByCities;
        }elseif($cityKladr && !$terminalId){
            foreach($arCities as $city){
                if($city->code == $cityKladr){
                    $cityData = array(
                        'cityName'  => $city->name,
                        'cityID'    => $city->cityID,
                        'cityKladr' => $city->code,
                        'terminals' => $city->terminals->terminal
                    );
                    $arCityTerminals = $cityData;
                    break 1;
                }
            }
            $arTerminalsList = $arCityTerminals;
        }elseif($cityKladr && $terminalId){
            foreach($arCities as $city){
                if($city->code == $cityKladr){
                    foreach($city->terminals->terminal as $terminal){
                        if($terminal->id == $terminalId){
                            $terminal->cityName = $city->name;
                            $terminal->cityID = $city->cityID;
                            break 2;
                        }
                    }
                }
            }
            $arTerminalsList['terminal'] = $terminal;
        }
        $arTerminalsList['hash'] = $hash;
        return $arTerminalsList;
    }

    /**
     * Получение списка стран
     * @param array $apiKey ключ API
     * @return array
     */
    public static function GetCountries($apiKey){
        $url = json_decode(self::sendCurl(self::$apiUrls['countriesList'],json_encode(array('appkey'=>$apiKey))))->url;
        $countriesString = self::sendCurl($url,array(),"GET","string");
        $rows = explode("\n",$countriesString);
        $arCountriesList = array();
        $keys = str_getcsv($rows[0]);
        foreach($rows as $num=>$row){
            if($num != 0){
                $arValues = str_getcsv($row);
                $arRow = array();
                if($arValues[0]){
                    foreach($arValues as $index=>$value){
                        $arRow[$keys[$index]] = $value;
                    }
                    $arCountriesList[$arRow['countryUID']] = $arRow;
                }

            }
        }
        return $arCountriesList;
    }

    /**
     * Получить кладр региона (возвращает кладр первого попавшегося города в регионе. Только для приблизительных расчетов)
     * @param $apiKey ключ API
     * @param $locationName Название региона
     * @return bool|mixed
     */
    public static function GetRegionKladr($apiKey,$locationName){
        $response = json_decode(self::sendCurl(self::$apiUrls['cities'],json_encode(array('appkey'=>$apiKey))));
        if(isset($response)){
            $url = $response->url;
            $citiesString = self::sendCurl($url,array(),"GET","string");
            $rows = explode("\n",$citiesString);
            $keys = str_getcsv($rows[0]);

            foreach($rows as $num=>$row){
                if($num != 0){
                    $arValues = str_getcsv($row);
                    $arRow = array();
                    if($arValues[0]){
                        foreach($arValues as $index=>$value){
                            $arRow[$keys[$index]] = $value;
                        }
                        $matches = array();
                        preg_match('/(\(.+\))/',$arRow['name'],$matches);

                        if(isset($matches[0])){
                            if(strpos($matches[0],$locationName) !== false){
                                return $arRow['codeKLADR'];
                            }
                        }
                    }

                }
            }
        }
        return false;
    }

}
