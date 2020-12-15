<?php

/**
 *
 * @copyright 	2020 Anton Bashurov
 * @link 		https://rock-n-scroll.ru
 * @author 		Anton Bashurov
 *
 */

session_start();

chdir ('../../');
require_once('delivery/dellinii/Dellinii.php');
require_once('api/Simpla.php');
$dellinii = new DellindeliveryApicore();
$simpla = new Simpla();


$cart = $simpla->cart->get_cart();
$error = '';
$result = new stdClass();

// Очистим ошибки и цены, чтобы они не вылезли в заказе с другим рассчетом
unset($_SESSION['delivery_dellinii']['error']);
unset($_SESSION['delivery_dellinii']['errors']);
unset($_SESSION['delivery_dellinii']['price']);


if(!empty($cart->purchases))
{
    $total_weight = 0;
    $total_volume = 0;

    $max_length = 0;
    $max_height = 0;
    $max_width = 0;

    foreach ($cart->purchases as $purchase) {
        if(empty($error))
        {
            $length = floatval($purchase->variant->length);
            $width = floatval($purchase->variant->width);
            $height = floatval($purchase->variant->height);

            $volume = $width * $length * $height;

            if ($volume > 0 && floatval($purchase->variant->weight) > 0)
            {
                $total_weight += floatval($purchase->variant->weight) * intval($purchase->amount);
                $total_volume += $dellinii->ConvertVolumeFromCubicMillimeterToCubicMeter($volume) * intval($purchase->amount);

                if($length > $max_length)
                    $max_length = $length;
                if($width > $max_width)
                    $max_width = $width;
                if($height > $max_height)
                    $max_height = $height;
            }
            else
                $error = 'Ошибка габаритов товара.'; // Достаточно одного товара без размеров
        }
    }

    $max_length = $dellinii->convertMMtoM($max_length);
    $max_width  = $dellinii->convertMMtoM($max_width);
    $max_height = $dellinii->convertMMtoM($max_height);

    $simpla->design->assign('total_weight',	$total_weight);
    $simpla->design->assign('max_length',	$max_length);
    $simpla->design->assign('max_width',	$max_width);
    $simpla->design->assign('max_height',	$max_height);
}
else
{
    $error = 'Ошибка - Пустая корзина.';
}

// С корзиной все нормально? тогда проверим необходимые опции
if(empty($error))
{
    $delivery_settings = $simpla->delivery->get_delivery_settings($simpla->request->get('delivery_method', 'integer'));

    if(empty($delivery_settings['appkey']))
        $error = 'Ошибка настроек модуля - empty_appkey';
    elseif(empty($delivery_settings['derivalPoint']))
        $error = 'Ошибка настроек модуля - empty_derivalPoint';
}

// Нобходимо выбрать точку выдачи товара
if(empty($error))
{
    $terminals_arr = $dellinii->sendApiRequest('terminals', array('appkey' => $delivery_settings['appkey']));
    if(!empty($terminals_arr->errors))
    {
        $error = 'Ошибка сервиса деловых линий - terminals';
        $result->errors = $terminals_arr->errors;
    }
    elseif(!empty($terminals_arr->url))
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $terminals_arr->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded'
        ));
        $terminals = curl_exec($ch);

        //$responseInfo = curl_getinfo($ch);
        curl_close($ch);

        $terminals = json_decode(html_entity_decode($terminals), TRUE);
        $simpla->design->assign('terminals', $terminals);

        // Получим выбранные город и терминал
        $dellinii_city = $simpla->request->get('dellinii_city', 'integer');
        $dellinii_terminal = $simpla->request->get('dellinii_terminal');

        // Запомним в сессии чего мы там навыбирали, чтобы перезагрузив страницу получить выбранные
        if(empty($dellinii_city))
            if(!empty($_SESSION['delivery_dellinii']['dellinii_city']))
                $dellinii_city = $_SESSION['delivery_dellinii']['dellinii_city'];
        else
            $_SESSION['delivery_dellinii']['dellinii_city'] = $dellinii_city;

        if(empty($dellinii_terminal))
            if(!empty($_SESSION['delivery_dellinii']['dellinii_terminal']))
                $dellinii_terminal = $_SESSION['delivery_dellinii']['dellinii_terminal'];
        else
            $_SESSION['delivery_dellinii']['dellinii_terminal'] = $dellinii_terminal;

        $simpla->design->assign('dellinii_city', $dellinii_city);
        $simpla->design->assign('dellinii_terminal', $dellinii_terminal);
    }
}


// Проверим объем и вес, есть ли у всех товаров, иначе считать нет смысла
if(empty($error) && !empty($dellinii_terminal))
{
    $count_array = [
        "appkey" => $delivery_settings['appkey'],
        "derivalPoint" => $delivery_settings['derivalPoint'], // Кладр места отправки
        // "derivalDoor" => true, // Если нужна отправка от адреса, а не от терминала
        "arrivalPoint" => $dellinii_terminal, // Кладр места получения
        // "arrivalDoor" => false, // Доставка до адреса
        // "servicekinduid" => "0x91C2110CC6B6430D4B1E2D59855A0E42", // Хер его проссы
        "sizedVolume" => $total_volume,
        "sizedWeight" => $total_weight,
        //"oversizedVolume" => 0.8, // Объем негабаритной части, нихера не ясно зачем это нужно
        //"oversizedWeight" => 23.1, // Вес негабаритной части, нихера не ясно зачем это нужно
        "length" => $max_length, // Макс длина
        "width" => $max_height, // Макс ширина
        "height" => $max_width, // Макс высота
    ];

    $delivery_answer = $dellinii->sendApiRequest('calculator', $count_array);
    if(empty($delivery_answer->errors))
    {
        $simpla->design->assign('delivery_answer',	$delivery_answer);
        $_SESSION['delivery_dellinii']['price'] = $delivery_answer->price;
        $_SESSION['delivery_dellinii']['time'] = $delivery_answer->time->nominative;
    }
    else
    {
        $error = 'Ошибка метода рассчета';
        $result->errors = $delivery_answer->errors;
    }

}

$result->error = $error;
$simpla->design->assign('error', $error);
$result->printed_tpl = $simpla->design->fetch('delivery/dellinii/design/template.tpl');

// Дополнительно для формирования заказа. нам необходимо записать в сессию не только
// нечитаемые айди, но и информацию для менеджера, куда нужно отпарвлять всю шляпу
// Или информацию почему не посчиталось
if(!empty($result->error))
{
    // чтобы случайно не передать барахла в заказ, сбросим если пусто
    unset($_SESSION['delivery_dellinii']['delivery_info']);
    unset($_SESSION['delivery_dellinii']['price']);
    $_SESSION['delivery_dellinii']['error'] = $result->error;
    $_SESSION['delivery_dellinii']['errors'] = $result->errors;
}
else
{
    foreach ($terminals['city'] as $city)
    {
        if($city['cityID'] == $dellinii_city)
            foreach ($city['terminals']['terminal'] as $terminal)
            {
                if($terminal['addressCode']['street_code'] == $dellinii_terminal)
                    $_SESSION['delivery_dellinii']['delivery_info'] = $terminal['name'] . ' | ' . $terminal['fullAddress'] . ' | ' . $terminal['phones'][0]['number'];
            }
    }
    $result->delivery_info = $_SESSION['delivery_dellinii']['delivery_info'];
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
print json_encode($result);
