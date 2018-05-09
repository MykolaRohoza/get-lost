<?php 
class ControllerToolPriceHolder extends Controller { 
	private $error = array();
	private $ssl = 'SSL';
	private $prises_path = '';

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->ssl = (defined('VERSION') && version_compare(VERSION,'2.2.0.0','>=')) ? true : 'SSL';
		$this->prises_path = DIR_SYSTEM . 'storage/upload/prices/';
	}

	
	public function index() {
		$this->load->language('tool/price_holder');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('tool/price_holder');
		$this->getForm();
	}


	public function getSettings() 
	{
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('tool/price_holder');
			$json = array();
			if($this->request->post['path']){
				$json['settings'] = $this->model_tool_price_holder->getSettings($this->request->post['path']);
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
	public function add_price_to_db() 
	{
		$this->load->model('tool/price_holder');
		
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$file = $this->prises_path . $this->request->post['price_filename'];
			if (is_file($file)){
				$upload_data = $this->request->post;
				$upload_data['file'] = $file;
				if($this->model_tool_price_holder->addDataToDB($upload_data)){
					$this->session->data['success'] = $this->language->get('text_success_books');
					$this->model_tool_price_holder->saveSettings($upload_data, $this->request->post['price_filename']);
				}
			} else{
				$this->error['warning'] = $this->language->get('error_upload');
			}
			
		}
		$this->response->redirect($this->url->link('tool/price_holder', 'token=' . $this->session->data['token'], $this->ssl));
	}
	public function upload() {
		$this->load->language('tool/price_holder');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('tool/price_holder');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateUploadForm())) {
			
			$file = '';
			if(!$this->request->post['use_saved_file'] && isset( $this->request->files['upload'] ) && (is_uploaded_file($this->request->files['upload']['tmp_name']))){
				$file = $this->request->files['upload']['tmp_name'];
			} else {
				$file = $this->prises_path . $this->request->post['use_saved_file'];
			}
			
			if (is_file($file)){
//				$incremental = ($this->request->post['incremental']) ? true : false;
				$upload_data = array(
					'line_count' => ((!empty($this->request->post['line_count']))? (int)$this->request->post['line_count']: 0),
					'file' => $file,
					
				);
				if(!empty($this->request->post['not_null'])){
					$upload_data['not_null'] = $this->request->post['not_null'];
				}
				if(!empty($this->request->post['main_cell'])){
					$upload_data['main_cell'] = (int)$this->request->post['main_cell'];
				}
				
				if ($this->model_tool_price_holder->upload($upload_data)) {
					$this->session->data['success'] = $this->language->get('text_success');
					
					if(!empty($this->request->files['upload'])){
						$source = $this->request->files['upload']['tmp_name'];
						$dest = $this->prises_path . date('Y_m_d-H_i_s-') . $this->request->files['upload']['name'];
						if(copy($source, $dest)){
							$file = $dest;
						}
					}
					
					$this->session->data['price_reader'] = str_replace($this->prises_path, '', $file);
					
					$this->response->redirect($this->url->link('tool/price_holder', 'token=' . $this->session->data['token'], $this->ssl));
				} else {
					$this->error['warning'] = $this->language->get('error_upload');
					if (defined('VERSION')) {
						if (version_compare(VERSION,'2.1.0.0') > 0) {
							$this->error['warning'] .= "<br />\n".$this->language->get( 'text_log_details_2_1_x' );
						} else
							$this->error['warning'] .= "<br />\n".$this->language->get( 'text_log_details_2_0_x' );
					} else {
						$this->error['warning'] .= "<br />\n".$this->language->get( 'text_log_details' );
					}
				}
			}
		}

		$this->getForm();
	}


	protected function return_bytes($val)
	{
		$val = trim($val);
	
		switch (strtolower(substr($val, -1)))
		{
			case 'm': $val = (int)substr($val, 0, -1) * 1048576; break;
			case 'k': $val = (int)substr($val, 0, -1) * 1024; break;
			case 'g': $val = (int)substr($val, 0, -1) * 1073741824; break;
			case 'b':
				switch (strtolower(substr($val, -2, 1)))
				{
					case 'm': $val = (int)substr($val, 0, -2) * 1048576; break;
					case 'k': $val = (int)substr($val, 0, -2) * 1024; break;
					case 'g': $val = (int)substr($val, 0, -2) * 1073741824; break;
					default : break;
				} break;
			default: break;
		}
		return $val;
	}


	public function delete_books() {
		$this->load->language('tool/price_holder');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('tool/price_holder');
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_tool_price_holder->deleteBooks($this->request->post, $this->prises_path);
			$this->session->data['success'] = $this->language->get('text_success_delete_books');
			$this->response->redirect($this->url->link('tool/price_holder', 'token=' . $this->session->data['token'], $this->ssl));
		}
		$this->getForm();
	}


	protected function getForm() {
		$data = array();
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['exist_filter'] = 0;//$this->model_tool_price_holder->existFilter();

		$data['text_export_type_category'] = ($data['exist_filter']) ? $this->language->get('text_export_type_category') : $this->language->get('text_export_type_category_old');
		$data['text_export_type_product'] = ($data['exist_filter']) ? $this->language->get('text_export_type_product') : $this->language->get('text_export_type_product_old');
		$data['text_export_type_poa'] = $this->language->get('text_export_type_poa');
		$data['text_export_type_option'] = $this->language->get('text_export_type_option');
		$data['text_export_type_attribute'] = $this->language->get('text_export_type_attribute');
		$data['text_export_type_filter'] = $this->language->get('text_export_type_filter');
		$data['text_export_type_customer'] = $this->language->get('text_export_type_customer');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_loading_notifications'] = $this->language->get( 'text_loading_notifications' );
		$data['text_retry'] = $this->language->get('text_retry');
		
		$data['text_select_delete_books'] = $this->language->get('text_select_delete_books');
		$data['text_delete_file'] = $this->language->get('text_delete_file');
		$data['text_delete_settings'] = $this->language->get('text_delete_settings');
		$data['text_delete_products'] = $this->language->get('text_delete_products');

		$data['text_not_use_saved_file'] = $this->language->get( 'text_not_use_saved_file' );
		
		$data['entry_not_null'] = $this->language->get( 'entry_not_null' );
		$data['entry_line_count'] = $this->language->get( 'entry_line_count' );
		$data['entry_saved_files'] = $this->language->get( 'entry_saved_files' );
		$data['entry_main_cell'] = $this->language->get( 'entry_main_cell' );
		
		$data['help_not_null_no'] = $this->language->get( 'help_not_null_no' );
		$data['help_not_null_yes'] = $this->language->get( 'help_not_null_yes' );
		$data['help_line_count'] = $this->language->get( 'help_line_count' );
		$data['help_main_cell'] = $this->language->get( 'help_main_cell' );
		
		
		
		$data['entry_export'] = $this->language->get( 'entry_export' );
		$data['entry_import'] = $this->language->get( 'entry_import' );
		$data['entry_export_type'] = $this->language->get( 'entry_export_type' );
		$data['entry_range_type'] = $this->language->get( 'entry_range_type' );
		$data['entry_start_id'] = $this->language->get( 'entry_start_id' );
		$data['entry_start_index'] = $this->language->get( 'entry_start_index' );
		$data['entry_end_id'] = $this->language->get( 'entry_end_id' );
		$data['entry_end_index'] = $this->language->get( 'entry_end_index' );
		$data['entry_incremental'] = $this->language->get( 'entry_incremental' );
		$data['entry_upload'] = $this->language->get( 'entry_upload' );
		$data['entry_settings_use_option_id'] = $this->language->get( 'entry_settings_use_option_id' );
		$data['entry_settings_use_option_value_id'] = $this->language->get( 'entry_settings_use_option_value_id' );
		$data['entry_settings_use_attribute_group_id'] = $this->language->get( 'entry_settings_use_attribute_group_id' );
		$data['entry_settings_use_attribute_id'] = $this->language->get( 'entry_settings_use_attribute_id' );
		$data['entry_settings_use_filter_group_id'] = $this->language->get( 'entry_settings_use_filter_group_id' );
		$data['entry_settings_use_filter_id'] = $this->language->get( 'entry_settings_use_filter_id' );
		$data['entry_settings_use_export_cache'] = $this->language->get( 'entry_settings_use_export_cache' );
		$data['entry_settings_use_import_cache'] = $this->language->get( 'entry_settings_use_import_cache' );

		$data['tab_export'] = $this->language->get( 'tab_export' );
		$data['tab_import'] = $this->language->get( 'tab_import' );
		$data['tab_settings'] = $this->language->get( 'tab_settings' );
		$data['tab_worksheets'] = $this->language->get( 'tab_worksheets' );
		$data['tab_delete_books'] = $this->language->get( 'tab_delete_books' );

		$data['button_export'] = $this->language->get( 'button_export' );
		$data['button_import'] = $this->language->get( 'button_import' );
		$data['button_settings'] = $this->language->get( 'button_settings' );
		$data['button_export_id'] = $this->language->get( 'button_export_id' );
		$data['button_export_page'] = $this->language->get( 'button_export_page' );
		$data['button_send'] = $this->language->get( 'button_send' );

		$data['help_range_type'] = $this->language->get( 'help_range_type' );
		$data['help_incremental_yes'] = $this->language->get( 'help_incremental_yes' );
		$data['help_incremental_no'] = $this->language->get( 'help_incremental_no' );
		$data['help_import'] = ($data['exist_filter']) ? $this->language->get( 'help_import' ) : $this->language->get( 'help_import_old' );
		$data['help_format'] = $this->language->get( 'help_format' );

		$data['error_select_file'] = $this->language->get('error_select_file');
		$data['error_post_max_size'] = str_replace( '%1', ini_get('post_max_size'), $this->language->get('error_post_max_size') );
		$data['error_upload_max_filesize'] = str_replace( '%1', ini_get('upload_max_filesize'), $this->language->get('error_upload_max_filesize') );
		$data['error_id_no_data'] = $this->language->get('error_id_no_data');
		$data['error_page_no_data'] = $this->language->get('error_page_no_data');
		$data['error_param_not_number'] = $this->language->get('error_param_not_number');
		$data['error_notifications'] = $this->language->get('error_notifications');
		$data['error_no_news'] = $this->language->get('error_no_news');
		$data['error_batch_number'] = $this->language->get('error_batch_number');
		$data['error_min_item_id'] = $this->language->get('error_min_item_id');

		
		
		
		if (!empty($this->session->data['price_holder_error']['errstr'])) {
			$this->error['warning'] = $this->session->data['price_holder_error']['errstr'];
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
			if (!empty($this->session->data['price_holder_nochange'])) {
				$data['error_warning'] .= "<br />\n".$this->language->get( 'text_nochange' );
			}
		} else {
			$data['error_warning'] = '';
		}

		unset($this->session->data['price_holder_error']);
		unset($this->session->data['price_holder_nochange']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if(isset($this->session->data['price_reader'])){
			$data['worksheets'] = $this->load->controller('tool/worksheets');
		} else {
			$data['worksheets'] = $this->language->get('text_no_worksheets');
		}
		
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], $this->ssl)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('tool/price_holder', 'token=' . $this->session->data['token'], $this->ssl)
		);

		$data['back'] = $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], $this->ssl);
		$data['button_back'] = $this->language->get( 'button_back' );
		$data['import'] = $this->url->link('tool/price_holder/upload', 'token=' . $this->session->data['token'], $this->ssl);
		$data['export'] = $this->url->link('tool/price_holder/download', 'token=' . $this->session->data['token'], $this->ssl);
		$data['action_delete_books'] = $this->url->link('tool/price_holder/delete_books', 'token=' . $this->session->data['token'], $this->ssl);
		$data['post_max_size'] = $this->return_bytes( ini_get('post_max_size') );
		$data['upload_max_filesize'] = $this->return_bytes( ini_get('upload_max_filesize') );

		if (isset($this->request->post['export_type'])) {
			$data['export_type'] = $this->request->post['export_type'];
		} else {
			$data['export_type'] = 'p';
		}

		if (isset($this->request->post['not_null'])) {
			$data['not_null'] = $this->request->post['not_null'];
		} else {
			$data['not_null'] = 1;
		}
		if (isset($this->request->post['line_count'])) {
			$data['line_count'] = $this->request->post['line_count'];
		} else {
			$data['line_count'] = 10;
		}
		if (isset($this->request->post['main_cell'])) {
			$data['main_cell'] = $this->request->post['main_cell'];
		} else {
			$data['main_cell'] = 1;
		}

		if (isset($this->request->post['min'])) {
			$data['min'] = $this->request->post['min'];
		} else {
			$data['min'] = '';
		}
		
		if (isset($this->request->post['min'])) {
			$data['min'] = $this->request->post['min'];
		} else {
			$data['min'] = '';
		}

		if (isset($this->request->post['max'])) {
			$data['max'] = $this->request->post['max'];
		} else {
			$data['max'] = '';
		}

		if (isset($this->request->post['incremental'])) {
			$data['incremental'] = $this->request->post['incremental'];
		} else {
			$data['incremental'] = '1';
		}

		if (isset($this->request->post['price_holder_settings_use_option_id'])) {
			$data['settings_use_option_id'] = $this->request->post['price_holder_settings_use_option_id'];
		} else if ($this->config->get( 'price_holder_settings_use_option_id' )) {
			$data['settings_use_option_id'] = '1';
		} else {
			$data['settings_use_option_id'] = '0';
		}

		if (isset($this->request->post['price_holder_settings_use_option_value_id'])) {
			$data['settings_use_option_value_id'] = $this->request->post['price_holder_settings_use_option_value_id'];
		} else if ($this->config->get( 'price_holder_settings_use_option_value_id' )) {
			$data['settings_use_option_value_id'] = '1';
		} else {
			$data['settings_use_option_value_id'] = '0';
		}

		if (isset($this->request->post['price_holder_settings_use_attribute_group_id'])) {
			$data['settings_use_attribute_group_id'] = $this->request->post['price_holder_settings_use_attribute_group_id'];
		} else if ($this->config->get( 'price_holder_settings_use_attribute_group_id' )) {
			$data['settings_use_attribute_group_id'] = '1';
		} else {
			$data['settings_use_attribute_group_id'] = '0';
		}

		if (isset($this->request->post['price_holder_settings_use_attribute_id'])) {
			$data['settings_use_attribute_id'] = $this->request->post['price_holder_settings_use_attribute_id'];
		} else if ($this->config->get( 'price_holder_settings_use_attribute_id' )) {
			$data['settings_use_attribute_id'] = '1';
		} else {
			$data['settings_use_attribute_id'] = '0';
		}

		if (isset($this->request->post['price_holder_settings_use_filter_group_id'])) {
			$data['settings_use_filter_group_id'] = $this->request->post['price_holder_settings_use_filter_group_id'];
		} else if ($this->config->get( 'price_holder_settings_use_filter_group_id' )) {
			$data['settings_use_filter_group_id'] = '1';
		} else {
			$data['settings_use_filter_group_id'] = '0';
		}

		if (isset($this->request->post['price_holder_settings_use_filter_id'])) {
			$data['settings_use_filter_id'] = $this->request->post['price_holder_settings_use_filter_id'];
		} else if ($this->config->get( 'price_holder_settings_use_filter_id' )) {
			$data['settings_use_filter_id'] = '1';
		} else {
			$data['settings_use_filter_id'] = '0';
		}

		if (isset($this->request->post['price_holder_settings_use_export_cache'])) {
			$data['settings_use_export_cache'] = $this->request->post['price_holder_settings_use_export_cache'];
		} else if ($this->config->get( 'price_holder_settings_use_export_cache' )) {
			$data['settings_use_export_cache'] = '1';
		} else {
			$data['settings_use_export_cache'] = '0';
		}

		if (isset($this->request->post['price_holder_settings_use_import_cache'])) {
			$data['settings_use_import_cache'] = $this->request->post['price_holder_settings_use_import_cache'];
		} else if ($this->config->get( 'price_holder_settings_use_import_cache' )) {
			$data['settings_use_import_cache'] = '1';
		} else {
			$data['settings_use_import_cache'] = '0';
		}
		
		
		$data['saved_files'] = $this->getSavedFiles();
			
		
		
		

		$min_product_id = 0;//$this->model_tool_price_holder->getMinProductId();
		$max_product_id = 0;//$this->model_tool_price_holder->getMaxProductId();
		$count_product = 0;//$this->model_tool_price_holder->getCountProduct();
		$min_category_id = 0;//$this->model_tool_price_holder->getMinCategoryId();
		$max_category_id = 0;//$this->model_tool_price_holder->getMaxCategoryId();
		$count_category = 0;//$this->model_tool_price_holder->getCountCategory();
		$min_customer_id = 0;//$this->model_tool_price_holder->getMinCustomerId();
		$max_customer_id = 0;//$this->model_tool_price_holder->getMaxCustomerId();
		$count_customer = 0;//$this->model_tool_price_holder->getCountCustomer();
		
		$data['min_product_id'] = $min_product_id;
		$data['max_product_id'] = $max_product_id;
		$data['count_product'] = $count_product;
		$data['min_category_id'] = $min_category_id;
		$data['max_category_id'] = $max_category_id;
		$data['count_category'] = $count_category;
		$data['min_customer_id'] = $min_customer_id;
		$data['max_customer_id'] = $max_customer_id;
		$data['count_customer'] = $count_customer;

		$data['token'] = $this->session->data['token'];

		$this->document->addStyle('view/stylesheet/price_holder.css');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view( ((version_compare(VERSION, '2.2.0.0') >= 0) ? 'tool/price_holder' : 'tool/price_holder.tpl'), $data));
	}

	protected function getSavedFiles() 
	{
		$result = array();
		$files = glob($this->prises_path . '*');
		if($files){
			foreach ($files as $file) {
				$tmp_file_name = explode('/', str_replace('\\', '/', $file));
				$result[] = $tmp_file_name[count($tmp_file_name)-1];
			}
		}
		return $result;
	
	}




	protected function validateUploadForm() {
		if (!$this->user->hasPermission('modify', 'tool/price_holder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} 


		if(!$this->request->post['use_saved_file']){
			if (!isset($this->request->files['upload']['name'])) {
				if (isset($this->error['warning'])) {
					$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_name' );
				} else {
					$this->error['warning'] = $this->language->get( 'error_upload_name' );
				}
			} else {
				$ext = strtolower(pathinfo($this->request->files['upload']['name'], PATHINFO_EXTENSION));
				if (($ext != 'xls') && ($ext != 'xlsx') && ($ext != 'ods')) {
					if (isset($this->error['warning'])) {
						$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_ext' );
					} else {
						$this->error['warning'] = $this->language->get( 'error_upload_ext' );
					}
				}
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}


	protected function validateSettingsForm() {
		if (!$this->user->hasPermission('access', 'tool/price_holder')) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}

		if (empty($this->request->post['price_holder_settings_use_option_id'])) {
			$option_names = $this->model_tool_price_holder->getOptionNameCounts();
			foreach ($option_names as $option_name) {
				if ($option_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_name['name'], $this->language->get( 'error_option_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['price_holder_settings_use_option_value_id'])) {
			$option_value_names = $this->model_tool_price_holder->getOptionValueNameCounts();
			foreach ($option_value_names as $option_value_name) {
				if ($option_value_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_value_name['name'], $this->language->get( 'error_option_value_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['price_holder_settings_use_attribute_group_id'])) {
			$attribute_group_names = $this->model_tool_price_holder->getAttributeGroupNameCounts();
			foreach ($attribute_group_names as $attribute_group_name) {
				if ($attribute_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_group_name['name'], $this->language->get( 'error_attribute_group_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['price_holder_settings_use_attribute_id'])) {
			$attribute_names = $this->model_tool_price_holder->getAttributeNameCounts();
			foreach ($attribute_names as $attribute_name) {
				if ($attribute_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_name['name'], $this->language->get( 'error_attribute_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['price_holder_settings_use_filter_group_id'])) {
			$filter_group_names = $this->model_tool_price_holder->getFilterGroupNameCounts();
			foreach ($filter_group_names as $filter_group_name) {
				if ($filter_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_group_name['name'], $this->language->get( 'error_filter_group_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['price_holder_settings_use_filter_id'])) {
			$filter_names = $this->model_tool_price_holder->getFilterNameCounts();
			foreach ($filter_names as $filter_name) {
				if ($filter_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_name['name'], $this->language->get( 'error_filter_name' ) );
					return false;
				}
			}
		}

		return true;
	}


	public function getNotifications() {
		sleep(1); // give the data some "feel" that its not in our system
		$this->load->model('tool/price_holder');
		$this->load->language( 'tool/price_holder' );
		$response = $this->model_tool_price_holder->getNotifications();
		$json = array();
		if ($response===false) {
			$json['message'] = '';
			$json['error'] = $this->language->get( 'error_notifications' );
		} else {
			$json['message'] = $response;
			$json['error'] = '';
		}
		$this->response->setOutput(json_encode($json));
	}
}
?>