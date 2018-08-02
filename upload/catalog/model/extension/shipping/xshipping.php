<?php
class ModelExtensionShippingXshipping extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/xshipping');
	
		$method_data = array();
	    $quote_data = array();
		$currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');

		if (!isset($address['zone_id'])) $address['zone_id'] = '';
		if (!isset($address['country_id'])) $address['country_id'] = '';

		$shipping_xshipping_methods = $this->config->get('shipping_xshipping_methods');

		if (!is_array($shipping_xshipping_methods) || !$shipping_xshipping_methods) return array(); 
			
			
      		for($i=1;$i<=12;$i++)
			{

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$shipping_xshipping_methods['geo_zone_id'.$i] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
	
				if (!$shipping_xshipping_methods['geo_zone_id'.$i]) {
					$status = true;
				} elseif ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
				
				
				
				if (!$shipping_xshipping_methods['status'.$i]) {
				  $status = false;
				}
				
				if (!$shipping_xshipping_methods['name'.$i]) {
				  $status = false;
				}
				
				$shipping_cost = (float)$shipping_xshipping_methods['cost'.$i];
				$free_shipping_cost=(float)$shipping_xshipping_methods['free'.$i];
				if(empty($free_shipping_cost)) $free_shipping_cost = 0;
				
				if ($this->cart->getSubTotal() >= $free_shipping_cost && $free_shipping_cost!=0) {
					$shipping_cost = 0;
				}
				
				if ($status) {
				
					$quote_data['xshipping'.$i] = array(
						'code'         => 'xshipping'.'.xshipping'.$i,
						'title'        => $shipping_xshipping_methods['name'.$i],
						'cost'         => $shipping_cost,
						'tax_class_id' => $shipping_xshipping_methods['tax_class_id'.$i],
						'sort_order'   => intval($shipping_xshipping_methods['sort_order'.$i]),
						'text'         => $this->currency->format($this->tax->calculate($shipping_cost, $shipping_xshipping_methods['tax_class_id'.$i], $this->config->get('config_tax')), $currency_code)
					);
				}
			}
			
			if(!$quote_data) return array(); 

			$sort_order = array();
			foreach ($quote_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
            array_multisort($sort_order, SORT_ASC, $quote_data);

			//print_r($quote_data);
			
			$method_data = array(
				'code'       => 'xshipping',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_xshipping_sort_order'),
				'error'      => ''
			);	
		
	
		return $method_data;
	}
}
?>