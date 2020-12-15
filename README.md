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

### Разберемся с api/, можно ипользовать приложенные файлы, но если стоят иные модификации, или же это клон симплы

##### Научим варианты товаров сохранять и вызывать габариты api/Variants.php:


1. get_variants:

    $query = $this->db->placehold("
            SELECT
                v.id,
                v.product_id ,
                v.price,
                NULLIF(v.compare_price, 0) as compare_price,
                v.sku,
                v.unit,
                IFNULL(v.stock, ?) as stock,
                (v.stock IS NULL) as infinity,
                v.name,
                v.attachment,
                v.position,
                v.length,
                v.height,
                v.width,
                v.weight
            FROM __variants AS v
            WHERE 1
                $product_id_filter
				$variant_id_filter
				$instock_filter
            ORDER BY v.position
        ", $this->settings->max_order_amount);

2. get_variant:

    $query = $this->db->placehold("
            SELECT
                v.id,
                v.product_id ,
                v.price,
                NULLIF(v.compare_price, 0) as compare_price,
                v.sku,
                v.unit,
                IFNULL(v.stock, ?) as stock,
                (v.stock IS NULL) as infinity,
                v.name,
                v.attachment,
                v.length,
                v.height,
                v.width,
                v.weight
            FROM __variants v
            WHERE v.id=?
            LIMIT 1
        ", $this->settings->max_order_amount, $id);



##### Научим сохранять и вызывать данные по методам доставки api/Delivery.php:

    // Функция вызова модулей доставки
	function get_delivery_modules()
	{
		$modules_dir = $this->config->root_dir.'delivery/';

		$modules = array();
		$handler = opendir($modules_dir);
		while ($dir = readdir($handler))
		{
			$dir = preg_replace("/[^A-Za-z0-9]+/", "", $dir);
			if (!empty($dir) && $dir != "." && $dir != ".." && is_dir($modules_dir.$dir))
			{

				if(is_readable($modules_dir.$dir.'/settings.xml') && $xml = simplexml_load_file($modules_dir.$dir.'/settings.xml'))
				{
					$module = new stdClass;

					$module->name = (string)$xml->name;
					$module->settings = array();

					foreach($xml->settings as $setting)
					{
						$module->settings[(string)$setting->variable] = new stdClass;
						$module->settings[(string)$setting->variable]->name = (string)$setting->name;
						$module->settings[(string)$setting->variable]->variable = (string)$setting->variable;
						$module->settings[(string)$setting->variable]->variable_options = array();
						foreach($setting->options as $option)
						{
							$module->settings[(string)$setting->variable]->options[(string)$option->value] = new stdClass;
							$module->settings[(string)$setting->variable]->options[(string)$option->value]->name = (string)$option->name;
							$module->settings[(string)$setting->variable]->options[(string)$option->value]->value = (string)$option->value;
						}
					}
					$modules[$dir] = $module;
				}

			}
		}
		closedir($handler);
		return $modules;

	}

	function get_delivery_settings($method_id)
	{
		$query = $this->db->placehold("SELECT settings FROM __delivery WHERE id=? LIMIT 1", intval($method_id));
		$this->db->query($query);
		$settings = $this->db->result('settings');

		$settings = unserialize($settings);
		return $settings;
	}

	public function update_delivery_settings($method_id, $settings)
	{
		if(!is_string($settings))
		{
			$settings = serialize($settings);
		}
		$query = $this->db->placehold("UPDATE __delivery SET settings=? WHERE id in(?@) LIMIT 1", $settings, (array)$method_id);
		$this->db->query($query);
		return $method_id;
	}
