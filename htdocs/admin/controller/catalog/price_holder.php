<?php
class ControllerCatalogPriceHolder extends Controller {
	private $error = array();

	protected $allowable_cell_values = array(
		'name',
		'model',
		'year',
		'manufacturer',
		'short_size',
		'code',
		'price',
		'price_small_wholesale',
		'price_middle_wholesale',
		'price_big_wholesale',
		'quantity',
//		'diameter',
		'country',
		'company_name',
		'php_id',
	 );
	public function index($settings) {
		$this->language->load('catalog/price_holder');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/price_holder');

	}
	/**
	 * 
	 * @param type $filter_data
	 * @return string html-template
	 */

	public function getProductForm($filter_data)
	{
		$template = '';
		$this->load->model('catalog/price_holder');
		$this->load->language('tool/worksheets');
		$this->load->language('catalog/price_holder');
		
		$products = $this->model_catalog_price_holder->getProducts($filter_data);
		$data = array();
		$data['keys'] = $this->allowable_cell_values;

		foreach ($data['keys'] as $value) {
			$data['text_' . $value] = $this->language->get('text_' . $value);
			$data['help_' . $value] = $this->language->get('help_' . $value);
		}
		if($products){
			$data['products'] = $products;
			$template = $this->load->view('catalog/price_holder.tpl', $data);
		}
		return $template;
	}
	public function search() {
		if ($this->validateSearchForm()) {

			$filter_data = array();
			foreach ($this->request->get as $get_key => $get_value) {
				if(preg_match('/^filter_.*$/', $get_key)){
					$filter_data[$get_key] = $get_value;
				}
			}
			
			$this->response->setOutput($this->getProductForm($filter_data));
		}
	}


	protected function validateSearchForm() {
		if (!$this->user->hasPermission('modify', 'catalog/price_holder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}


}
