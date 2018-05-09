<?php

static $registry = null;

// Error Handler
function error_handler_for_price_holder($errno, $errstr, $errfile, $errline) {
	global $registry;
	
	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errors = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$errors = "Fatal Error";
			break;
		default:
			$errors = "Unknown";
			break;
	}
	
	$config = $registry->get('config');
	$url = $registry->get('url');
	$request = $registry->get('request');
	$session = $registry->get('session');
	$log = $registry->get('log');
	
	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $errors . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}

	if (($errors=='Warning') || ($errors=='Unknown')) {
		return true;
	}

	if (($errors != "Fatal Error") && isset($request->get['route']) && ($request->get['route']!='tool/price_holder/download'))  {
		if ($config->get('config_error_display')) {
			echo '<b>' . $errors . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
		}
	} else {
		$session->data['price_holder_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
		$token = $request->get['token'];
		$link = $url->link( 'tool/price_holder', 'token='.$token, 'SSL' );
		header('Status: ' . 302);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $link));
		exit();
	}

	return true;
}


function fatal_error_shutdown_handler_for_price_holder()
{
	$last_error = error_get_last();
	if ($last_error['type'] === E_ERROR) {
		// fatal error
		error_handler_for_price_holder(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}


class ModelToolPriceHolder extends Model {

	private $error = array();
	protected $null_array = array();
	
	
	
	public function __construct($registry)
	{
		
		parent::__construct($registry);
	}
	protected function clean( &$str, $allowBlanks=false ) {
		$result = "";
		$n = strlen( $str );
		for ($m=0; $m<$n; $m++) {
			$ch = substr( $str, $m, 1 );
			if (($ch==" ") && (!$allowBlanks) || ($ch=="\n") || ($ch=="\r") || ($ch=="\t") || ($ch=="\0") || ($ch=="\x0B")) {
				continue;
			}
			$result .= $ch;
		}
		return $result;
	}


	protected function multiquery( $sql ) {
		foreach (explode(";\n", $sql) as $sql) {
			$sql = trim($sql);
			if ($sql) {
				$this->db->query($sql);
			}
		}
	}


	protected function startsWith( $haystack, $needle ) {
		if (strlen( $haystack ) < strlen( $needle )) {
			return false;
		}
		return (substr( $haystack, 0, strlen($needle) ) == $needle);
	}

	protected function endsWith( $haystack, $needle ) {
		if (strlen( $haystack ) < strlen( $needle )) {
			return false;
		}
		return (substr( $haystack, strlen($haystack)-strlen($needle), strlen($needle) ) == $needle);
	}


	protected function getDefaultLanguageId() {
		$code = $this->config->get('config_language');
		$sql = "SELECT language_id FROM `".DB_PREFIX."language` WHERE code = '$code'";
		$result = $this->db->query( $sql );
		$language_id = 1;
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$language_id = $row['language_id'];
				break;
			}
		}
		return $language_id;
	}


	protected function getLanguages() {
		$query = $this->db->query( "SELECT * FROM `".DB_PREFIX."language` WHERE `status`=1 ORDER BY `code`" );
		return $query->rows;
	}





	protected function validateIncrementalOnly( &$reader, $incremental ) {
		// certain worksheets can only be imported in incremental mode for the time being
		$ok = true;
		$worksheets = array( 'Customers', 'Addresses' );
		foreach ($worksheets as $worksheet) {
			$data = $reader->getSheetByName( $worksheet );
			if ($data) {
				if (!$incremental) {
					$msg = $this->language->get( 'error_incremental_only' );
					$msg = str_replace( '%1', $worksheet, $msg );
					$this->log->write( $msg );
					$ok = false;
				}
			}
		}
		return $ok;
	}


	protected function validateUpload( &$reader )
	{
		$ok = true;
		return $ok;
	}


	protected function clearCache() {
		$this->cache->delete('*');
	}

	public function saveSettings($data, $filename)
	{
		$query = $this->db->query("SELECT phs_id FROM " . DB_PREFIX . "price_holder_settings WHERE `path` = '" . $this->db->escape($filename) . "'");
		if($query->num_rows){
			$this->db->query("UPDATE " . DB_PREFIX . "price_holder_settings SET `settings` = '" . $this->db->escape(json_encode($data)) . "' WHERE phs_id='" . (int)$query->row['phs_id'] . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "price_holder_settings SET `path` = '" . $this->db->escape($filename) . "', `settings` = '" . $this->db->escape(json_encode($data)) . "'");
		}
	}

	public function getSettings($filename) {
		$setting_data = array();

		$query = $this->db->query("SELECT settings FROM " . DB_PREFIX . "price_holder_settings WHERE `path` = '" . $this->db->escape($filename) . "'");
		if($query->num_rows){
			$setting_data = json_decode($query->row['settings'], true);
		}

		return $setting_data;
	}
	public function getSavedSettingPathes() {
		$setting_pathes = array();

		$query = $this->db->query("SELECT path FROM " . DB_PREFIX . "price_holder_settings");

		foreach ($query->rows as $result) {
			$setting_pathes[] = $result['path'];
		}

		return $setting_pathes;
	}
	public function deleteBooks($data, $path)
	{
		if(!empty($data['files'])){
			foreach ($data['files'] as $book) {
				if(!isset($book['file_name'])){
					continue;
				}
				if(!empty($book['delete_file']) && is_file($path . $book['file_name'])){
					unlink($path . $book['file_name']);
				}
				if(isset($book['delete_settings'])){
					$this->db->query("DELETE FROM `" . DB_PREFIX . "price_holder_settings` WHERE path='" . $this->db->escape($book['file_name']) . "'");
				}
			}
		} else {
			return false;
		}
	}
	public function addDataToDB( $upload_data)
	{
		// we use our own error handler
		global $registry;
		
		$company_id = $this->getCompanyId($upload_data['company_name']);
		$filename = $upload_data['file'];
		$update = ((!empty($upload_data['update']))? true: false);
		$update_fields = ((!empty($upload_data['update_fields']))? $upload_data['update_fields']: array());


		$registry = $this->registry;
		set_error_handler('error_handler_for_price_holder', E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_price_holder');
		
		try {
			$this->session->data['price_holder_nochange'] = 1;

			// we use the PHPExcel package from http://phpexcel.codeplex.com/
			$cwd = getcwd();
			chdir( DIR_SYSTEM.'PHPExcel' );
			require_once( 'Classes/PHPExcel.php' );
			chdir( $cwd );
			
			// Memory Optimization
			if ($this->config->get( 'price_holder_settings_use_import_cache' )) {
				$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
				$cacheSettings = array( ' memoryCacheSize '  => '16MB'  );
				PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			}

			// parse uploaded spreadsheet file
			$inputFileType = PHPExcel_IOFactory::identify($filename);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$reader = $objReader->load($filename);
			
			// read the various worksheets and load them to the database
			if (!$this->validateIncrementalOnly( $reader, $update )) {
				return false;
			}
			if (!$this->validateUpload( $reader )) {
				return false;
			}
			$worksheets_data = array();
			
			$worksheets = $reader->getAllSheets();
			
			foreach ($worksheets as $worksheet_key => $worksheet) {

				$cell_settings = $this->getCellSettings($upload_data, $worksheet_key);
				if(!$cell_settings){
					continue;
				}

				$rows = array();
				$num_row = $worksheet->getHighestRow();
				if(!empty($upload_data['line_count'])){
					$valid_num_row = (int)$upload_data['line_count'];
				} else {
					$valid_num_row = $num_row;
				}
				
				for ($row_index = $valid_row_index = 1; $row_index <= $num_row && $valid_row_index <= $valid_num_row; $row_index++) {
					
					$row = $this->getValidatedNamedRow($worksheet, $row_index, $cell_settings);
					if($row){
						$row['phc_id'] = $company_id;
						$this->addRowToDB($row, (($update && !empty($update_fields))? $update_fields: false));
						if(!empty($upload_data['use_cache'])){
							$last_saved_row = array(
								'phc_id' => $row['phc_id'],
								'row_index' => $row_index,
								'worksheet_key' => $worksheet_key,
							);
						}
						$valid_row_index++;
					}
				}
				
				if(!empty($last_saved_row)){
					$this->cache->set('price_reader.last_saved_row', $last_saved_row);
				}
			}


			
//			$this->clearCache();

			return true;
		} catch (Exception $e) {
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$this->session->data['price_holder_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return false;
		}
	}
	
	protected function getCompanyId($company_name)
	{
		$company_query = $this->db->query("SELECT phc_id FROM `" . DB_PREFIX. "price_holder_company` WHERE company_name='" . $this->db->escape($company_name) . "'");
		if($company_query->num_rows){
			return (int)$company_query->row['phc_id'];
		}
		$this->db->query("INSERT INTO `" . DB_PREFIX. "price_holder_company` SET company_name='" . $this->db->escape($company_name) . "'");
		return (int)$this->db->getLastId();
	}
	protected function addRowToDB($row, $update_fields = false)
	{
		$php_id = false;
		if($update_fields){
			$sql = "SELECT php_id FROM `" . DB_PREFIX. "price_holder_products` WHERE ";
			foreach ($update_fields as $update_item) {
				if(array_key_exists($update_item, $row)){
					$sql .= " `" . $update_item . "`='" . $row[$update_item] . "' AND "; 
				}
			}
			$sql .= " `phc_id`='" . $row['phc_id'] . "'"; 
			$query = $this->db->query($sql);
			if($query->num_rows === 1){
				$php_id = (int)$query->row['php_id'];
			}
		}
		
		if(!$php_id){
			$sql = "INSERT INTO `";
		} else {
			$sql = "UPDATE `";

		}
		$sql .= DB_PREFIX. "price_holder_products` SET ";
		
		$li_row = count($row) - 1;
		$i_row = 0;
		foreach ($row as $key => $value) {
			$sql .= " `" . $key . "`='" . $value . "'"; 
			if($i_row < $li_row){
				$sql .= ","; 
			}
			$i_row++;
		}
		if($php_id){
			$sql .= " WHERE `php_id`='" . $php_id . "'";
		}
		$this->db->query($sql);
		return (int)$this->db->getLastId();
	}
	protected function getCellSettings($upload_data, $worksheet_key)
	{
		$cell_settings = array();
		if(!empty($upload_data['filter_data'])){
			if($upload_data['single_settings'] == 'single'){
				foreach ($upload_data['filter_data'] as $key => $value) {
					$cell_settings = $value;
					break;
				}
			} elseif(!empty($upload_data['filter_data'][$worksheet_key])) {
				$cell_settings = $upload_data['filter_data'][$worksheet_key];
			}
		}
		return $cell_settings;
		
	}


	public function upload( $upload_data) {
		// we use our own error handler
		global $registry;
		$filename = $upload_data['file'];
		$incremental = ((!empty($upload_data['incremental']))? true: false);
		
		$registry = $this->registry;
		set_error_handler('error_handler_for_price_holder',E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_price_holder');

		try {
			$this->session->data['price_holder_nochange'] = 1;

			// we use the PHPExcel package from http://phpexcel.codeplex.com/
			$cwd = getcwd();
			chdir( DIR_SYSTEM.'PHPExcel' );
			require_once( 'Classes/PHPExcel.php' );
			chdir( $cwd );
			
			// Memory Optimization
			if ($this->config->get( 'price_holder_settings_use_import_cache' )) {
				$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
				$cacheSettings = array( ' memoryCacheSize '  => '16MB'  );
				PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			}

			// parse uploaded spreadsheet file
			$inputFileType = PHPExcel_IOFactory::identify($filename);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$reader = $objReader->load($filename);

			
			// read the various worksheets and load them to the database
			if (!$this->validateIncrementalOnly( $reader, $incremental )) {
				return false;
			}
			if (!$this->validateUpload( $reader )) {
				return false;
			}
			$worksheets_data = array();
			
			$worksheets = $reader->getAllSheets();

			foreach ($worksheets as $key => $worksheet) {
				$worksheet_title = $worksheet->getTitle();
				$worksheet_highest_row = $worksheet->getHighestRow();
				
				
				$filter = array();
				if(!empty($upload_data['not_null']) && !empty($upload_data['main_cell'])){
					$main_cell = (int)$upload_data['main_cell'] - 1;
					if($main_cell < 0) {
						$main_cell = 0;
					}
					$filter[$main_cell] = array(
							'not_null',
							'not_empty'
						);
				}

				$rows = array();
				$num_row = $worksheet->getHighestRow();
				if(!empty($upload_data['line_count'])){
					$valid_num_row = (int)$upload_data['line_count'];
				} else {
					$valid_num_row = $num_row;
				}
				for ($row_index = $valid_row_index = 1; $row_index <= $num_row && $valid_row_index <= $valid_num_row; $row_index++) {
					$row = $this->getValidatedRow($worksheet, $row_index, $filter);
					if($row){
						$rows[] = $row;
						$valid_row_index++;
					}
				}
				if($rows){
					$worksheets_data[] = array(
						'title' => $worksheet_title,
						'highest_row' => $worksheet_highest_row,
						'rows' => $rows,
					);
				}
			}

			if($worksheets_data){
				$this->cache->delete('price_reader.worksheets_data');
				$this->cache->set('price_reader.worksheets_data', $worksheets_data);
				
			}

			
			$this->clearCache();
			$this->session->data['price_holder_nochange'] = 0;
			$available_product_ids = array();
//			$this->uploadProducts( $reader, $incremental, $available_product_ids );

			return true;
		} catch (Exception $e) {
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$this->session->data['price_holder_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return false;
		}
	}
	
	protected function getValidatedNamedRow($worksheet, $row_index, $cell_data)
	{
		$row = array();
		$is_valid_row = true;
		$num_cell = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());
		for ($cell_index = 0; $cell_index < $num_cell; $cell_index++) {
			$value = $worksheet->getCellByColumnAndRow($cell_index, $row_index)->getValue();
			$filter = array();
			if(!empty($cell_data[$cell_index])){
				$cell_name = $cell_data[$cell_index]['cell_value'];
				$row[$cell_name] = $value;
				if(!empty($cell_data[$cell_index]['filters'])){
					$filter = $cell_data[$cell_index]['filters'];
				}
			}
			if(!$this->checkValue($value, false, $filter)){
				$is_valid_row = false;
			}
			if(!$is_valid_row) {
				return false;
			}
		}
		return $row;
	}
	
	protected function getValidatedRow($worksheet, $row_index, $filter)
	{
		$row = array();
		$is_valid_row = true;
		$num_cell = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());
		for ($cell_index = 0; $cell_index < $num_cell; $cell_index++) {
			$value = $worksheet->getCellByColumnAndRow($cell_index, $row_index)->getValue();
			$row[] = $value;
			if(!$this->checkValue($value, $cell_index, $filter)){
				$is_valid_row = false;
			}
		}
		if(!$is_valid_row){
			$row = array();
		}
		return $row;
	}
	protected function checkValue($value, $cell_index, $filter)
	{	
		$result = true;
		$filter_data = array();
		if($cell_index === false){
			$filter_data = $filter;
		} elseif(!empty($filter[$cell_index])){
			$filter_data = $filter[$cell_index];
		}

		if(!empty($filter_data) ){
			foreach ($filter_data as $filter_value) {
				switch ($filter_value) {
					case 'not_null':
						if(is_null($value) ){
							$result = false;
						}
						break;
					case 'not_empty':
						if(empty($value) ){
							$result = false;
						}
						break;
					case 'dectimal':
					case 'integer':
						if(!is_numeric($value) ){
							$result = false;
						}
						break;

					default:
						break;
				}
				if(!$result){
					return false;
				}
			}
		}
		
		return $result;
	}

}
?>