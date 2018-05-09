<?php 
class ControllerToolWorksheets extends Controller { 
	 protected $allowable_cell_values = array(
		'model',
		'name',
//		'diameter',
		'price',
		'price_small_wholesale',
		'price_middle_wholesale',
		'price_big_wholesale',
		'quantity',
		'manufacturer',
		'code',
		'country',
		'year',
		'short_size',
	 );
	 protected $allowable_filters = array(
//		'not_null',
		'not_empty',
		'integer',
		'dectimal',
	 );
	 
	public function index() {

		$data = array();
		$this->load->language('tool/worksheets');
		$this->load->model('tool/price_holder');
		$data['entry_allowable_cell_values'] = $this->language->get('entry_allowable_cell_values');
		
		$data['text_cell'] = $this->language->get('text_cell');
		$data['text_settings'] = $this->language->get('text_settings');
		$data['text_worksheet'] = $this->language->get('text_worksheet');
		$data['text_select_filter'] = $this->language->get('text_select_filter');
		$data['text_select_allowable_cell_value'] = $this->language->get('text_select_allowable_cell_value');
		$data['text_download_settings'] = $this->language->get('text_download_settings');
		
		
		$data['text_cell'] = $this->language->get('text_cell');
		$data['text_filter'] = $this->language->get('text_filter');
		$data['text_select_allowable_cell_value'] = $this->language->get('text_select_allowable_cell_value');
		$data['text_price_settings'] = $this->language->get('text_price_settings');
		$data['text_company_name'] = $this->language->get('text_company_name');
		$data['text_update'] = $this->language->get('text_update');
		
		
		$data['text_single_settings'] = $this->language->get('text_single_settings');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_worksheet_settings'] = $this->language->get('text_none');
		$data['text_ignore_without_settings'] = $this->language->get('text_ignore_without_settings');
		
		$data['text_no'] = $this->language->get('text_no');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['entry_update_fields'] = $this->language->get('entry_update_fields');
		
		$data['error_no_relations'] = $this->language->get('error_no_relations');
		$data['error_not_enough_relations'] = $this->language->get('error_not_enough_relations');
		$data['error_company_name'] = $this->language->get('error_company_name');
		
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_add_filter'] = $this->language->get('button_add_filter');
		
		$data['help_filter'] = $this->language->get('help_filter');
		$data['help_update_fields'] = $this->language->get('help_update_fields');
		
		$data['worksheets_action'] = $this->url->link('tool/price_holder/add_price_to_db', 'token='. $this->session->data['token']);
		
		$data['token'] = $this->session->data['token'];
		$data['worksheets_data'] = $this->cache->get('price_reader.worksheets_data');
		$data['price_filename'] = $this->session->data['price_reader'];
		if(!$data['worksheets_data']){
			$data['worksheets_data'] = array();
		}
		$data['settings'] = $this->model_tool_price_holder->getSavedSettingPathes();
		
		$data['update_fields'] = array('name');
		$data['allowable_cell_values'] = $this->getAllowableValues($this->allowable_cell_values);
		$data['allowable_filters'] = $this->getAllowableValues($this->allowable_filters);
		
		return $this->load->view( ((version_compare(VERSION, '2.2.0.0') >= 0) ? 'tool/worksheets' : 'tool/worksheets.tpl'), $data);
	}
	
	protected function getAllowableValues($allowable_cell_values, $language_prefix = 'text_')
	{
		$result = array();
		$sort_order =  array();
		foreach ($allowable_cell_values as $allowable_cell_value) {
			$label = $this->language->get($language_prefix . $allowable_cell_value);
			$result[] = array(
				'key' => $allowable_cell_value,
				'label' =>  $label,
			);
			$sort_order[] = $label;
		}
		if($sort_order && count($result) === count($sort_order)){
			array_multisort($sort_order, $result, SORT_LOCALE_STRING);
		}
		
		return $result;
	}


}
?>