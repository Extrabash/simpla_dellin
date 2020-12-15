# simpla_dellin
## Модуль расчета доставки деловые линии


#### Добавим необходимые поля в базу,


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
            ADD `delivery_info` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `modified`;

---

#### Разберемся с api/, можно ипользовать приложенные файлы, но если стоят иные модификации, или же это клон симплы

###### Научим варианты товаров сохранять и вызывать габариты api/Variants.php:

Обновить из соответсвующего файла функции:

    public function get_variant($id)

    public function get_variants($filter = array())

В оба запроса добавятся поля:

    v.length,
    v.height,
    v.width,
    v.weight


###### Научим сохранять и вызывать данные по методам доставки api/Delivery.php:

Добавить из соответсвующего файла функции:

    // Функция вызова модулей доставки
	function get_delivery_modules()

	function get_delivery_settings($method_id)

	public function update_delivery_settings($method_id, $settings)

Обновить функции:

    public function get_delivery($id)

    public function get_deliveries($filter = array())

Добавится поле:

    , module


###### Вызовем новое поле api/Orders.php:

Обновить из соответсвующего файла функции:

    public function get_order($id)

    function get_orders($filter = array())

Добавится:

    , o.delivery_info

---

#### Научим админку работать с новыми данными

###### Габариты в simpla/design/html/product.tpl:

Смотреть соответствующий файл:

    {* Модульная доставка 1 - Заголовок вариантов *}
    <li class="variant_sizes variant_length">Длин.</li>
    <li class="variant_sizes variant_width">Шир.</li>
    <li class="variant_sizes variant_height">Выс.</li>
    <li class="variant_sizes variant_weight">Вес</li>
    {* Модульная доставка 1 end *}

    {* Модульная доставка 2 - Все варианты *}
    <li class="variant_sizes variant_length">
        <input name="variants[length][]" type="text" value="{$variant->length}" />
    </li>
    <li class="variant_sizes variant_width">
        <input name="variants[width][]" type="text" value="{$variant->width}" />
    </li>
    <li class="variant_sizes variant_height">
        <input name="variants[height][]" type="text" value="{$variant->height}" />
    </li>
    <li class="variant_sizes variant_weight">
        <input name="variants[weight][]" type="text" value="{$variant->weight}" />
    </li>
    {* Модульная доставка 2 end*}

    {* Модульная доставка 3 - новый вариант *}
    <li class="variant_sizes variant_length">
        <input name="variants[length][]" type="text" value="" />
    </li>
    <li class="variant_sizes variant_width">
        <input name="variants[width][]" type="text" value="" />
    </li>
    <li class="variant_sizes variant_height">
        <input name="variants[height][]" type="text" value="" />
    </li>
    <li class="variant_sizes variant_weight">
        <input name="variants[weight][]" type="text" value="" />
    </li>
    {* Модульная доставка 3 end*}

Плюс стили в simpla/design/css/style.css, ну или куда вам угодно:

    .variant_sizes {
        width: 52px;
    }

    .variant_sizes input {
        width: 42px;
    }

    #variants_block li.variant_amount {
        width: 71px;
    }

    .variant_sizes.variant_height {
        width: 57px;
    }

##### Выведем модуль в шаблон корзины

##### Добавим к оформлению заказа
