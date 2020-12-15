{* Вкладки *}
{capture name=tabs}
	{if in_array('settings', $manager->permissions)}<li><a href="index.php?module=SettingsAdmin">Настройки</a></li>{/if}
	{if in_array('currency', $manager->permissions)}<li><a href="index.php?module=CurrencyAdmin">Валюты</a></li>{/if}
	<li class="active"><a href="index.php?module=DeliveriesAdmin">Доставка</a></li>
	{if in_array('payment', $manager->permissions)}<li><a href="index.php?module=PaymentMethodsAdmin">Оплата</a></li>{/if}
	{if in_array('managers', $manager->permissions)}<li><a href="index.php?module=ManagersAdmin">Менеджеры</a></li>{/if}
{/capture}

{if $delivery->id}
{$meta_title = $delivery->name scope=parent}
{else}
{$meta_title = 'Новый способ доставки' scope=parent}
{/if}

{* Подключаем Tiny MCE *}
{include file='tinymce_init.tpl'}


{if $message_success}
<!-- Системное сообщение -->
<div class="message message_success">
	<span class="text">{if $message_success == 'added'}Способ доставки добавлен{elseif $message_success == 'updated'}Способ доставки изменен{/if}</span>
	{if $smarty.get.return}
	<a class="button" href="{$smarty.get.return}">Вернуться</a>
	{/if}
</div>
<!-- Системное сообщение (The End)-->
{/if}

{if $message_error}
<!-- Системное сообщение -->
<div class="message message_error">
	<span class="text">{if $message_error == 'empty_name'}Не указано название доставки{/if}</span>
	<a class="button" href="">Вернуться</a>
</div>
<!-- Системное сообщение (The End)-->
{/if}


<!-- Основная форма -->
<form method=post id=product enctype="multipart/form-data">
<input type=hidden name="session_id" value="{$smarty.session.id}">
	<div id="name">
		<input class="name" name=name type="text" value="{$delivery->name|escape}"/>
		<input name=id type="hidden" value="{$delivery->id}"/>
		<div class="checkbox">
			<input name=enabled value='1' type="checkbox" id="active_checkbox" {if $delivery->enabled}checked{/if}/> <label for="active_checkbox">Активен</label>
		</div>
	</div>

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

	<!-- Левая колонка свойств товара -->
	<div id="column_left">

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

		<!-- Параметры страницы -->
		<div class="block layer">
			<h2>Стоимость доставки</h2>
			<ul>
				<li><label class=property>Стоимость</label><input name="price" class="simpla_small_inp" type="text" value="{$delivery->price}" /> {$currency->sign}</li>
				<li><label class=property>Бесплатна от</label><input name="free_from" class="simpla_small_inp" type="text" value="{$delivery->free_from}" /> {$currency->sign}</li>
				<li><label class=property for="separate_payment">Оплачивается отдельно</label><input id="separate_payment" name="separate_payment" type="checkbox" value="1" {if $delivery->separate_payment}checked{/if} /></li>
			</ul>
		</div>
		<!-- Параметры страницы (The End)-->

	</div>
	<!-- Левая колонка свойств товара (The End)-->

	<!-- Левая колонка свойств товара -->
	<div id="column_right">
		<div class="block layer">
		<h2>Возможные способы оплаты</h2>
		<ul>
		{foreach $payment_methods as $payment_method}
			<li>
			<input type=checkbox name="delivery_payments[]" id="payment_{$payment_method->id}" value='{$payment_method->id}' {if in_array($payment_method->id, $delivery_payments)}checked{/if}> <label for="payment_{$payment_method->id}">{$payment_method->name}</label><br>
			</li>
		{/foreach}
		</ul>
		</div>
	</div>
	<!-- Левая колонка свойств товара (The End)-->

	<!-- Описагние товара -->
	<div class="block layer">
		<h2>Описание</h2>
		<textarea name="description" class="editor_small">{$delivery->description|escape}</textarea>
	</div>
	<!-- Описание товара (The End)-->
	<input class="button_green button_save" type="submit" name="" value="Сохранить" />

</form>
<!-- Основная форма (The End) -->

