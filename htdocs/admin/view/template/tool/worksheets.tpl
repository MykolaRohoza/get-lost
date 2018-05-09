<div class="panel panel-default">
	<div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i><?=$text_price_settings?></h3>
	</div>
	<div class="panel-body">
		
		<form action="<?= $worksheets_action ?>" method="post" enctype="multipart/form-data"
			  id="form-price_holder_settings" class="form-horizontal">
			<div class="form-group">
				<label class="col-sm-2 control-label" for="settings-path-for-download"><?=$text_download_settings?></label>
				<div class="col-sm-8">
					<select  class="form-control" id="settings-path-for-download">
					<?php foreach ($settings as $path) {?>
						<option value="<?=$path?>"><?=$path?></option>
					<?php } ?>
					</select>
				</div>
				<div class="col-sm-2"><input type="button" onclick="downloadSettings();" class="btn btn-primary pull-right" value="<?=$text_download_settings?>"></div>
			</div>
				<input type="hidden" name="price_filename" value="<?=$price_filename?>">
			<div id="price_holder_json_settings" style="display:none;">
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="input-company_name"><?=$text_company_name?></label>
				<div class="col-sm-10">
					<input type="text" name="company_name" value="" placeholder="<?=$text_company_name?>" id="input-company_name" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="input-update"><?=$text_update?></label>
				<div class="col-sm-10">
					<select name="update" class="form-control" id="input-update">
						<option value="0"
							<?php if(empty($update)){echo 'selected="selected"';}?>><?=$text_no?></option>
						<option value="1"
							<?php if(!empty($update)){echo 'selected="selected"';}?>><?=$text_yes?></option>
					</select>
				</div>
			</div>
			<div class="form-group">
                <label class="col-sm-2 control-label" for="input-update_fields"><span data-toggle="tooltip" title="<?php echo $help_update_fields; ?>"><?php echo $entry_update_fields; ?></span></label>
                <div class="col-sm-10">
                  <div id="product-update_fields" class="well well-sm" style="height: 150px; overflow: auto;">
					<?php $acv_i = 0; foreach ($allowable_cell_values as $allowable_cell_value) { ?>
						<div id="update_field-<?=$acv_i?>"> 
							<input type="checkbox" name="update_fields[<?=$acv_i?>]"
								   <?php if(!empty($update_fields) && in_array($allowable_cell_value['key'], $update_fields)) {
									   echo 'checked="checked"';
								   }?>
								   value="<?= $allowable_cell_value['key'] ?>" />
							<?= $allowable_cell_value['label'] ?>
						</div>
					<?php $acv_i++;} ?>
                  </div>
                </div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="input-single_settings"><?=$text_single_settings?></label>
				<div class="col-sm-10">
					<select name="single_settings" class="form-control">
						<option value="0"
							<?php if(empty($worksheet_settings)){echo 'selected="selected"';}?>><?=$text_none?></option>
						<option value="ignore"
							<?php if(!empty($worksheet_settings) && $worksheet_settings == 'ignore'){
								echo 'selected="selected"';
								
							}?>><?=$text_ignore_without_settings?></option>
						<option value="single"
							<?php if(!empty($worksheet_settings) && $worksheet_settings == 'single'){
								echo 'selected="selected"';
								
							}?>><?=$text_single_settings?></option>
						
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10">
					<input type="button" onclick="confirm();" class="btn btn-primary pull-right" value="Обработать">
				</div>
			</div>
        </form>
	</div>
</div>
		<?php foreach ($worksheets_data as $worksheet_index => $worksheet) { ?>
		<div class="table-responsive" id="worksheets_data-<?= $worksheet_index ?>">
			<table class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th class="text-left" colspan="<?= $worksheet['highest_row'] ?>"><?= $worksheet['title'] ?></th>	
					</tr>
				</thead>
				<tbody>
					<?php foreach ($worksheet['rows'] as $row) { ?>
						<tr>
							<?php foreach ($row as $cell_index => $cell) { ?>
								<td class="text-left" onclick="showCellSettigs(<?= $worksheet_index ?>, '<?= $worksheet['title'] ?>', <?= $cell_index ?>)">
									<?= $cell ?>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } ?>
<div class="modal fade" id="selectPrice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form id="worksheet-settings">
                    <div class="row">
                        <div class="form-group"> <h2><?= $text_settings ?></h2></div>
                        <div class="form-group"> <h3><?= $text_worksheet ?> «<span></span>»</h3></div>
                        <div class="form-group"> <h4><?= $text_cell ?> «<span></span>»</h4></div>
                        <div class="form-group">
                            <label for="modal-allowable_cell_values"><?= $entry_allowable_cell_values; ?></label>
							<select id="modal-allowable_cell_value" class="form-control">
								<option value="0"><?= $text_select_allowable_cell_value ?></option>
								<?php foreach ($allowable_cell_values as $allowable_cell_value) { ?>
										<option value="<?= $allowable_cell_value['key'] ?>"><?= $allowable_cell_value['label'] ?></option>
									<?php } ?>
							</select>
                        </div>
                        <div class="form-group">
							<div class="table-responsive" id="modal-filter">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr>
											<th class="text-left"><span data-toggle="tooltip" title="<?php echo $help_filter; ?>"><?= $text_filter; ?></span></th>	
											<th class="text-left" style="width:10%"></th>	
										</tr>
									</thead>
									<tbody>

									</tbody>
									<tfoot>
										<tr>
											<td colspan="1"></td>
											<td class="text-left">
												<button type="button" onclick="addFilter();"
														data-toggle="tooltip"
														title="<?= $button_add_filter; ?>"
														class="btn btn-primary">
													<i class="fa fa-plus-circle"></i>
												</button>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
                        </div>
                        <div class="col-sm-12">
							<input type="hidden" value="" id="modal-worksheet_index"/>
							<input type="hidden" value="" id="modal-cell_index"/>
                            <input type="button" onclick="addWorksheetData()" value="Применить" data-dismiss="modal" class="btn btn-primary">
							<input type="button" onclick="clearWorksheetData()" value="Удалить" data-dismiss="modal" class="btn btn-default">
							<input type="button" value="Отмена" data-dismiss="modal" class="btn btn-default">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style id="style-holder">
</style>

<script type="text/javascript"><!--
	var filter_row = 0, 
		filter_data = {},
		tmp_filter_data1 = {},
		errors = {}, 
		worksheets_count = <?=count($worksheets_data)?>;

	function downloadSettings()
	{

		$.ajax({
				url: 'index.php?route=tool/price_holder/getSettings&token=<?php echo $token; ?>',
				type: 'post',
				data: 'path=' + $('#settings-path-for-download').val(),
				success: function(json) {

					if (typeof json['settings'] === 'object') {
						$('#input-company_name').val(json.settings.company_name);
						$('select[name="single_settings"]').val(json.settings.single_settings);
						tmp_filter_data1 = json.settings.filter_data;
						filter_data = getFilterDataFromJson(json.settings.filter_data);
						validStyles();
						addWorksheetDataToSettings();
						if(typeof json.settings.update_fields !=='undefined' && Object.keys(json.settings.update_fields).length){
							addUpdateFieldsToSettings(json.settings.update_fields);
						}
						if(typeof json.settings.update !=='undefined' && json.settings.update){
							$('#input-update').val(1);
						}
					} 
				},
				failure: function(){
//					$('#price_holder_notification').html('<i class="fa fa-info-circle"></i><button type="button" class="close" data-dismiss="alert">&times;</button> '+'<?php echo $error_notifications; ?> <span style="cursor:pointer;font-weight:bold;text-decoration:underline;float:right;" onclick="getNotifications();"><?php echo $text_retry; ?></span>');
				},
				error: function() {
//					$('#price_holder_notification').html('<i class="fa fa-info-circle"></i><button type="button" class="close" data-dismiss="alert">&times;</button> '+'<?php echo $error_notifications; ?> <span style="cursor:pointer;font-weight:bold;text-decoration:underline;float:right;" onclick="getNotifications();"><?php echo $text_retry; ?></span>');
				}
			});
	}

	function addUpdateFieldsToSettings(update_fields)
	{
		$('input[type="checkbox"][name*="update_fields\["]').removeAttr('checked');
		$.each(update_fields, function(update_field_key, update_field_value){
			$('input[type="checkbox"][value="' + update_field_value + '"]').click();
		});
	}
	function getFilterDataFromJson(json_filter_data)
	{
		var tmp_filter_data = {};
		
		if(Object.keys(json_filter_data).length){
			$.each(json_filter_data, function(worksheet_index, worksheet_value){
				
				tmp_filter_data[worksheet_index] = {};
				if(Object.keys(worksheet_value).length){
					
					$.each(worksheet_value, function(cell_index, cell_value){
						tmp_filter_data[worksheet_index][cell_index] = {};
						tmp_filter_data[worksheet_index][cell_index]['cell_value'] = cell_value['cell_value'];
						
						if(typeof cell_value['filters'] !== 'undefined' && Object.keys(cell_value['filters']).length){
							tmp_filter_data[worksheet_index][cell_index]['filters'] = {};
							$.each(cell_value['filters'], function (filter_key, filter) {
								tmp_filter_data[worksheet_index][cell_index]['filters'][filter_key] = filter;
							});
						}
					});
				}

			});
		}
	
		return tmp_filter_data;
	}
	function confirm()
	{
		if(checkSettings()){
			addWorksheetDataToSettings();
			$('#form-price_holder_settings').submit();
		} else {
			printErrors(errors);
		}
	}
	
	function addWorksheetDataToSettings()
	{
		var html = '';
		$.each(filter_data, function(worksheet_index, worksheet_value){
			if (typeof worksheet_value === 'object' || typeof worksheet_value === 'array') {
				$.each(worksheet_value, function(cell_index, cell_value){
					if (typeof cell_value === 'object') {
						if (typeof cell_value['cell_value'] !== 'undefined') {
							html += getItemHTML(worksheet_index, cell_index, 'cell_value', false, cell_value['cell_value']);
						}
						if (typeof cell_value['filters'] !== 'undefined' && typeof cell_value['filters'] === 'object') {
							$.each(cell_value['filters'], function(filter_index, filter_value){
								html += getItemHTML(worksheet_index, cell_index, 'filters', filter_index, filter_value);
							});
						}
					}
				});
			}
		});
		$('#price_holder_json_settings').html(html);
	}
	function getItemHTML(worksheet_index, cell_index, value_name, value_index, value)
	{
		var result = '';
		result += '<input type="hidden" name="filter_data';
		result += '[' +  worksheet_index + ']';
		result += '[' + cell_index + ']';
		result += '[' + value_name + ']';
		if(value_index !== false){
			result += '[' + value_index + ']';
		}
		result += '"';
		result += 'value="' + value + '" >' + "\n";
		return result;
	}
	function checkSettings()
	{
		errors = {};
		if (typeof filter_data !== 'object'){
			errors.filter_data = '<?=$error_no_relations?>';
			return false;
		}
		
		if (!Object.keys(filter_data).length){
			errors.filter_data = '<?=$error_no_relations?>';
			return false;
		} 
		if(Object.keys(filter_data).length === 1 && worksheets_count > 1 && $('select[name="single_settings"]').val() === "0"){
			errors.not_enough_filter_data = '<?=$error_not_enough_relations?>';
		}
		if($('#input-company_name').val() === ""){
			errors.company_name = '<?=$error_company_name?>';
		}
		
		return !Object.keys(errors).length;
	}

	function printErrors(errors)
	{
		$('.alert-danger').remove();
		$.each(errors, function(error_name, error_message){
			printError(error_message);
		});
	}
	function printError(error_message)
	{
		var html = '';
		html += '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + error_message + "\n";
		html += '	<button type="button" class="close" data-dismiss="alert">&times;</button>' + "\n";
		html += '</div>' + "\n";
		$(html).insertBefore('#content>div.container-fluid>.panel-default');
		
	}
	function showCellSettigs(worksheet_index, worksheet_title, cell_index)
	{
		validateSettings(worksheet_index, worksheet_title, cell_index);
	}

	
	function clearSettings($modal_form)
	{
		$modal_form.find('#modal-filter table tbody').empty();
		$('#modal-allowable_cell_value').val(0);
		filter_row = 0;
	}

	function clearWorksheetData(worksheet_index, cell_index)
	{
		if (typeof filter_data[worksheet_index] === 'object'
				&& typeof filter_data[worksheet_index][cell_index] === 'object') {

			delete filter_data[worksheet_index][cell_index];
		}
		validStyles();

	}
	function validateSettings(worksheet_index, worksheet_title, cell_index)
	{
		var $modal = $('#selectPrice'), $modal_form = $('.modal-content .modal-body form#worksheet-settings');

		$modal.modal();
		$modal_form.find('h3 span').html(worksheet_title);
		$modal_form.find('h4 span').html((cell_index + 1));

		$('#modal-worksheet_index').val(worksheet_index);
		$('#modal-cell_index').val(cell_index);

		clearSettings($modal_form);

		if (typeof filter_data[worksheet_index] !== 'object') {
			return false;
		}
		if (typeof filter_data[worksheet_index][cell_index] !== 'object') {
			return false;
		}
		if (typeof filter_data[worksheet_index][cell_index]['cell_value'] !== 'undefined') {
			$('#modal-allowable_cell_value').val(filter_data[worksheet_index][cell_index]['cell_value']);
		}
		if (typeof filter_data[worksheet_index][cell_index]['filters'] === 'object' ) {
			$.each(filter_data[worksheet_index][cell_index]['filters'], function (key, filter) {
				addFilter();
				$('#filter-row-' + (filter_row - 1) + ' td [name="filters\[\]"]').val(filter);
			});
		}


	}


	function validStyles()
	{
		var style_html = '';

		if (typeof filter_data !== 'object') {
			return false;
		}
		$.each(filter_data, function (worksheet_index, worksheet) {
			if (typeof worksheet === 'object') {
				$.each(worksheet, function (column_num, column_val) {
					style_html += addGreenBorderToColumn(worksheet_index, (parseInt(column_num) + 1));
				});
			}
		});
		$('#style-holder').html(style_html);


	}
	function addWorksheetData()
	{
		var $modal_form = $('.modal-content .modal-body form#worksheet-settings'),
				worksheet_index = $('#modal-worksheet_index').val(),
				cell_index = $('#modal-cell_index').val(),
				cell_value = $('#modal-allowable_cell_value').val(),
				$filters = $modal_form.find('[name="filters\[\]"]');

		if (typeof filter_data[worksheet_index] !== 'object') {
			filter_data[worksheet_index] = {};
		}
		if (typeof filter_data[worksheet_index][cell_index] !== 'object') {
			filter_data[worksheet_index][cell_index] = {};
		}
		if (typeof filter_data[worksheet_index][cell_index]['filters'] !== 'object') {
			filter_data[worksheet_index][cell_index]['filters'] = {};
		}
		filter_data[worksheet_index][cell_index]['cell_value'] = cell_value;

		if ($filters.length) {
			$.each($filters, function (filter_key, filter) {
				var $filter = $(filter);
				if ($filter.val()) {
					filter_data[worksheet_index][cell_index]['filters'][filter_key] = $filter.val();
				}
			});
		}
		validStyles();

	}

	function addGreenBorderToColumn(worksheet_index, column_num)
	{
		var html = '';
		html += '#worksheets_data-' + worksheet_index + ' table tbody td:nth-child(' + column_num + '){' + "\n";
		html += '	-webkit-box-shadow: inset 0px 0px 0px 3px rgba(69,143,50,1);' + "\n";
		html += '	-moz-box-shadow: inset 0px 0px 0px 3px rgba(69,143,50,1);' + "\n";
		html += '	box-shadow: inset 0px 0px 0px 3px rgba(69,143,50,1);' + "\n";
		html += '}' + "\n";
		return html;
	}




	function addFilter() {
		var html = '';
		html += '<tr id="filter-row-' + filter_row + '">';
		html += '  <td class="text-left">';
		html += '		<select name="filters[]" class="form-control">';
		html += '			<option value="0"><?= $text_select_filter ?></option>';
	<?php foreach ($allowable_filters as $allowable_filter) { ?>
		html += '			<option value="<?= $allowable_filter['key'] ?>"><?= $allowable_filter['label'] ?></option>';
	<?php } ?>
		html += '		</select>';
		html += '  </td>';
		html += '  <td class="text-left"><button type="button" onclick="$(\'#filter-row-' + filter_row + '\').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
		html += '</tr>';

		$('#modal-filter table tbody').append(html);
		filter_row++;
	}
//--></script>