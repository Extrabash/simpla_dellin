# simpla_dellin
## Модуль расчета доставки деловые линии


##### Добавим необходимые поля в базу,


1. Поля габаритов для Варианта товара - s_variants:

    ALTER TABLE `s_variants`
        ADD `length` DECIMAL(9,4) NOT NULL AFTER `external_id`,
        ADD `height` DECIMAL(9,4) NOT NULL AFTER `length`,
        ADD `width` DECIMAL(9,4) NOT NULL AFTER `height`,
        ADD `weight` DECIMAL(9,4) NOT NULL AFTER `width`;

2. Поля для выбора модуля доставки и сохранения настроек модуля в таблице доставок - s_delivery:

    ALTER TABLE `s_delivery`
        ADD `module` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `separate_payment`,
        ADD `settings` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `module`;

3. Поле для сохранения данных от модуля в заказе - s_orders:

    ALTER TABLE `s_orders`
        ADD `delivery_info` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `delivery_info`;

---

##### Разберемся с api/, можно ипользовать приложенные файлы, но если стоят иные модификации, или же это клон симплы

1. Научим варианты товаров сохранять и вызывать габариты api/Variants.php:

Обновить из соответсвующего файла функции:

    public function get_variant($id)

    public function get_variants($filter = array())

В оба запроса добавятся поля:

    v.length,
    v.height,
    v.width,
    v.weight


2. Научим сохранять и вызывать данные по методам доставки api/Delivery.php:

Добавить из соответсвующего файла функции:

    // Функция вызова модулей доставки
	function get_delivery_modules()

	function get_delivery_settings($method_id)

	public function update_delivery_settings($method_id, $settings)
