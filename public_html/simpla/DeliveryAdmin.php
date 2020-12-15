<?PHP
require_once('api/Simpla.php');

class DeliveryAdmin extends Simpla
{

	public function fetch()
	{
		$delivery = new stdClass;
		if($this->request->method('post'))
		{
			$delivery->id               = $this->request->post('id', 'intgeger');
			$delivery->enabled          = $this->request->post('enabled', 'boolean');
			$delivery->name             = $this->request->post('name');
	 		$delivery->description      = $this->request->post('description');
	 		$delivery->price            = $this->request->post('price');
	 		$delivery->free_from        = $this->request->post('free_from');
			$delivery->separate_payment	= $this->request->post('separate_payment');

			// Модульная доставка 1/4
			$delivery->module			= $this->request->post('module', 'string');
			$delivery_settings 			= $this->request->post('delivery_settings');

	 		if(!$delivery_payments = $this->request->post('delivery_payments'))
	 			$delivery_payments = array();

			if(empty($delivery->name))
		        {
		            $this->design->assign('message_error', 'empty_name');
		        }
		        else
		        {
					if(empty($delivery->id))
					{
						$delivery->id = $this->delivery->add_delivery($delivery);
						$this->design->assign('message_success', 'added');
					}
					else
					{
						$this->delivery->update_delivery($delivery->id, $delivery);
						$this->design->assign('message_success', 'updated');
					}

					$this->delivery->update_delivery_payments($delivery->id, $delivery_payments);
					// Модульная доставка 2/4
					$this->delivery->update_delivery_settings($delivery->id, $delivery_settings);
		        }
		}
		else
		{
			$delivery->id = $this->request->get('id', 'integer');
			if(!empty($delivery->id))
			{
				$delivery = $this->delivery->get_delivery($delivery->id);
			}
			$delivery_payments = $this->delivery->get_delivery_payments($delivery->id);
			// Модульная доставка 3/4
			$delivery_settings = $this->delivery->get_delivery_settings($delivery->id);
		}

		$this->design->assign('delivery_payments', $delivery_payments);
		// Модульная доставка 4/4
		$this->design->assign('delivery_settings', $delivery_settings);

		// Все способы оплаты
		$payment_methods = $this->payment->get_payment_methods();
		$this->design->assign('payment_methods', $payment_methods);

		// Вызовем модули доставки в шаблон
		$delivery_modules = $this->delivery->get_delivery_modules();
		$this->design->assign('delivery_modules', $delivery_modules);

		$this->design->assign('delivery', $delivery);

  	  	return $this->design->fetch('delivery.tpl');
	}

}

