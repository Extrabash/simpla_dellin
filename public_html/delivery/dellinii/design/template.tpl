{* Запросим в обработчике и получим список городов, сразу *}

{if $terminals}
    <select name="dellinii_city" class="dellinii_step step_1" autocomplete="off" readonly
        onfocus="this.removeAttribute('readonly')">
        <option disabled {if !$dellinii_city}selected{/if} value="">Выберите город</option>
        {foreach $terminals['city'] as $city}
            <option value="{$city['cityID']}" {if $dellinii_city==$city['cityID']}selected{/if}>{$city['name']|escape}</option>
        {/foreach}
    </select>

    <select name="dellinii_terminal" {if !$dellinii_city}disabled{/if} class="dellinii_step step_2" autocomplete="off" readonly onfocus="this.removeAttribute('readonly')">
        <option disabled {if !$dellinii_terminal}selected{/if} value="">Выберите терминал</option>
        {if $dellinii_city}
        {foreach $terminals['city'] as $city}
            {if $dellinii_city==$city['cityID']}
                {foreach $city['terminals']['terminal'] as $terminal}
                    {if $terminal['giveoutCargo'] && $terminal['addressCode']['street_code'] &&
                        $terminal['maxWeight']  >= $total_weight &&
                        $terminal['maxLength']  >= $max_length &&
                        $terminal['maxWidth']   >= $max_width &&
                        $terminal['maxHeight']  >= $max_height}
                        <option
                            value="{$terminal['addressCode']['street_code']}"
                            {if $dellinii_terminal==$terminal['addressCode']['street_code']}selected{/if} data-maxWeight="{$terminal['maxWeight']}"
                            data-maxHeight="{$terminal['maxHeight']}"
                            data-maxWidth="{$terminal['maxWidth']}"
                            data-maxLength="{$terminal['maxLength']}">{$terminal['name']|escape} | {$terminal['address']|escape}</option>
                    {/if}
                {/foreach}
            {/if}
        {/foreach}
        {/if}
    </select>

    {if $delivery_answer->price > 0}
        <div>
            {$delivery_answer->price} <br />
            {$delivery_answer->time->nominative}
        </div>
    {/if}
{/if}

{if $error}
<div class="delivery_error" id="dellinii_delivery_error">
    {$error}
</div>
{/if}
