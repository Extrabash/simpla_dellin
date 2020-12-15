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


Смотреть соответствующий файл:

###### Габариты в simpla/design/html/product.tpl:

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

###### Модули и настройки в simpla/design/html/delivery.tpl:

    {* Модульная доставка 1 - выбор модуля *}
	<div id="product_categories">
	    <select name="module">
			<option value='null'>Ручная обработка</option>
	        {foreach $delivery_modules as $delivery_module}
    	        <option value='{$delivery_module@key|escape}' {if $delivery->module == $delivery_module@key}selected{/if} >{$delivery_module->name|escape}</option>
			{/foreach}
	    </select>
	</div>
	{* Модульная доставка 1 end *}

    {* Модульная доставка 2 - настрйоки модуля *}
		{if $delivery_modules[$delivery->module]->settings}
		<div class="block layer">
			<h2>Настройки - {$delivery_modules[$delivery->module]->name}</h2>
			{* Параметры модуля доставки *}
			<ul>
			   	{foreach $delivery_modules[$delivery->module]->settings as $setting}
    			    {$variable_name = $setting->variable}
    			    {if $setting->options|@count>1}
        			    <li><label class=property>{$setting->name}</label>
        			        <select name="delivery_settings[{$setting->variable}]">
        			            {foreach $setting->options as $option}
            			            <option value='{$option->value}' {if $option->value==$delivery_settings[$setting->variable]}selected{/if}>{$option->name|escape}</option>
        			            {/foreach}
        			        </select>
        			    </li>
    			    {elseif $setting->options|@count==1}
        			    {$option = $setting->options|@first}
        			    <li><label class="property" for="{$setting->variable}">{$setting->name|escape}</label><input name="delivery_settings[{$setting->variable}]" class="simpla_inp" type="checkbox" value="{$option->value|escape}" {if $option->value==$delivery_settings[$setting->variable]}checked{/if} id="{$setting->variable}" /> <label for="{$setting->variable}">{$option->name}</label></li>
    			    {else}
        			    <li><label class="property" for="{$setting->variable}">{$setting->name|escape}</label><input name="delivery_settings[{$setting->variable}]" class="simpla_inp" type="text" value="{$delivery_settings[$setting->variable]|escape}" id="{$setting->variable}" /></li>
    			    {/if}
			    {/foreach}
			</ul>
			{* END Параметры модуля доставки *}
		</div>
		{/if}
		{* Модульная доставка 2 end *}

###### Научимся это дело сохранять и вызывать в simpla/DeliveryAdmin.php:

    $delivery->separate_payment	= $this->request->post('separate_payment');

    // Модульная доставка 1
    $delivery->module			= $this->request->post('module', 'string');
    $delivery_settings 			= $this->request->post('delivery_settings');

Дальше

        $delivery_payments = $this->delivery->get_delivery_payments($delivery->id);
        // Модульная доставка 3
        $delivery_settings = $this->delivery->get_delivery_settings($delivery->id);
    }

    $this->design->assign('delivery_payments', $delivery_payments);
    // Модульная доставка 4
    $this->design->assign('delivery_settings', $delivery_settings);

##### Выведем модуль в шаблон корзины

##### Добавим к оформлению заказа
