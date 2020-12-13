<?php

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */

require_once('Simpla.php');

class Delivery extends Simpla
{

	public function get_delivery($id)
	{

		$query = $this->db->placehold("SELECT id, name, description, free_from, price, enabled, position, separate_payment, module FROM __delivery WHERE id=? LIMIT 1", intval($id));

		$this->db->query($query);
		return $this->db->result();
	}

	public function get_deliveries($filter = array())
	{
		// По умолчанию
		$enabled_filter = '';

		if(!empty($filter['enabled']))
			$enabled_filter = $this->db->placehold('AND enabled=?', intval($filter['enabled']));

		$query = "SELECT id, name, description, free_from, price, enabled, position, separate_payment, module
					FROM __delivery WHERE 1 $enabled_filter ORDER BY position";

		$this->db->query($query);

		return $this->db->results();
	}

	public function update_delivery($id, $delivery)
	{
		$query = $this->db->placehold("UPDATE __delivery SET ?% WHERE id in(?@)", $delivery, (array)$id);
		$this->db->query($query);
		return $id;
	}

	public function add_delivery($delivery)
	{
		$query = $this->db->placehold('INSERT INTO __delivery
		SET ?%',
		$delivery);

		if(!$this->db->query($query))
			return false;

		$id = $this->db->insert_id();
		$this->db->query("UPDATE __delivery SET position=id WHERE id=?", intval($id));
		return $id;
	}

	public function delete_delivery($id)
	{
		// Удаляем связь доставки с методоми оплаты
		$query = $this->db->placehold("SELECT payment_method_id FROM __delivery_payment WHERE delivery_id=?", intval($id));
		$this->db->query($query);

		if(!empty($id))
		{
			$query = $this->db->placehold("DELETE FROM __delivery WHERE id=? LIMIT 1", intval($id));
			$this->db->query($query);
		}
	}


	public function get_delivery_payments($id)
	{
		$query = $this->db->placehold("SELECT payment_method_id FROM __delivery_payment WHERE delivery_id=?", intval($id));
		$this->db->query($query);
		return $this->db->results('payment_method_id');
	}

	public function update_delivery_payments($id, $payment_methods_ids)
	{
		$query = $this->db->placehold("DELETE FROM __delivery_payment WHERE delivery_id=?", intval($id));
		$this->db->query($query);
		if(is_array($payment_methods_ids))
		foreach($payment_methods_ids as $p_id)
			$this->db->query("INSERT INTO __delivery_payment SET delivery_id=?, payment_method_id=?", $id, $p_id);
	}

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

}
