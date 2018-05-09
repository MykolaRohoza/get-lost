<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<a href="<?php echo $back; ?>" data-toggle="tooltip" title="<?php echo $button_back; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<?php if ($success) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>


		<div class="panel panel-default">
			<div class="panel-body">

				<ul class="nav nav-tabs">
					<li class="active"><a href="#tab-import" data-toggle="tab"><?php echo $tab_import; ?></a></li>
					<li><a href="#tab-delete_books" data-toggle="tab"><?php echo $tab_delete_books; ?></a></li>
					<li><a href="#tab-worksheets" data-toggle="tab"><?php echo $tab_worksheets; ?></a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="tab-import">
						<form action="<?php echo $import; ?>" method="post" enctype="multipart/form-data" id="import" class="form-horizontal">
							<table class="form">
								<tr>
									<td>
										<?php echo $entry_import; ?>
										<span class="help"><?php echo $help_import; ?></span>
										<span class="help"><?php echo $help_format; ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<span  data-toggle="tooltip" title="<?php echo $help_main_cell; ?>"><?php echo $entry_main_cell; ?></span><br /><br />
										<input type="text" name="main_cell" id="main_cell" value="<?=$main_cell?>"/>
									</td>
								</tr>
								<tr>
									<td>
										<?php echo $entry_not_null; ?><br />
										<?php if ($not_null) { ?>
										<input type="radio" name="not_null" value="1" checked="checked" />
										<span  data-toggle="tooltip" title="<?=$help_not_null_yes; ?>">
										<?=$text_yes?></span>
										<br />
										<input type="radio" name="not_null" value="0" />
										<span  data-toggle="tooltip" title="<?=$help_not_null_no?>"><?=$text_no?></span>
										<?php } else { ?>
										<input type="radio" name="not_null" value="1" />
										<span  data-toggle="tooltip" title="<?=$help_not_null_yes?>"><?=$text_yes?></span>
										<br />
										<input type="radio" name="not_null" value="0" checked="checked" />
										<span  data-toggle="tooltip" title="<?=$help_not_null_no?>"><?=$text_no?></span>
										<?php } ?>
									</td>
								</tr>
								<tr>
									<td>
										<?php echo $entry_saved_files; ?><br />
										<?php if (empty($use_saved_file)) { ?>
										<label style="width:100%;">
										<input type="radio" name="use_saved_file" value="0" checked="checked" />
											<?=$text_not_use_saved_file?>
										</label>
										
										<br />
										<?php } ?> 
										<?php foreach ($saved_files as $saved_file) { ?>
										<label >
										<input type="radio" name="use_saved_file" value="<?=$saved_file?>" />
											<?=$saved_file?>
										</label>
										<br />
										<?php } ?>
									</td>
								</tr>
								<tr>
									<td>
										<span  data-toggle="tooltip" title="<?php echo $help_line_count; ?>"><?php echo $entry_line_count; ?></span><br /><br />
										<input type="text" name="line_count" id="line_count"  value="<?=$line_count?>"/>
									</td>
								</tr>
								<tr>
									<td><?php echo $entry_upload; ?><br /><br /><input type="file" name="upload" id="upload" /></td>
								</tr>
								<tr>
									<td class="buttons"><a onclick="uploadData();" class="btn btn-primary"><span><?php echo $button_import; ?></span></a></td>
								</tr>
							</table>
						</form>
					</div>

					<div class="tab-pane" id="tab-delete_books">
						<form action="<?php echo $action_delete_books; ?>" method="post" enctype="multipart/form-data" id="settings" class="form-horizontal">
							<table class="form">

								<tbody>
									<?php foreach ($saved_files as $key_saved_file => $saved_file) {?>
									<tr>
										<td style="width:30%">
											<input type="hidden" name="files[<?=$key_saved_file?>][file_name]" value="<?=$saved_file?>">
											<label>
												<?=$saved_file?>
											</label>
										</td>
										<td>
											<label>
												<input type="checkbox" name="files[<?=$key_saved_file?>][delete_file]" value="1" /> <?=$text_delete_file; ?>
											</label>
										</td>
										<td>
											<label>
												<input type="checkbox" name="files[<?=$key_saved_file?>][delete_settings]" value="1" /> <?=$text_delete_settings; ?>
											</label>
										</td>
										<td>
											<label>
												<input type="checkbox" name="files[<?=$key_saved_file?>][delete_products]" value="1" /> <?=$text_delete_products; ?>
											</label>
										</td>
									</tr>
									<?php } ?>
									<tr>
										<td colspan="4" class="buttons"><input type="submit" class="btn btn-primary" value="<?php echo $button_send; ?>"></td>
									</tr>
								</tbody>
							</table>
						</form>
					</div>
					<div class="tab-pane" id="tab-worksheets">
						<?=$worksheets?>
					</div>

				</div>
			</div>
		</div>

	</div>

<script type="text/javascript"><!--


function check_range_type(export_type) {
	if ((export_type=='p') || (export_type=='c') || (export_type=='u')) {
		$('#range_type').show();
		$('#range_type_id').prop('checked',true);
		$('#range_type_page').prop('checked',false);
		$('.id').show();
		$('.page').hide();
	} else {
		$('#range_type').hide();
	}
}

$(document).ready(function() {

	check_range_type($('input[name=export_type]:checked').val());

	$("#range_type_id").click(function() {
		$(".page").hide();
		$(".id").show();
	});

	$("#range_type_page").click(function() {
		$(".id").hide();
		$(".page").show();
	});

	$('input[name=export_type]').click(function() {
		check_range_type($(this).val());
	});

	$('span.close').click(function() {
		$(this).parent().remove();
	});

	$('a[data-toggle="tab"]').click(function() {
		$('#price_holder_notification').remove();
	});


});

function checkFileSize(id) {
	// See also http://stackoverflow.com/questions/3717793/javascript-file-upload-size-validation for details
	var input, file, file_size;

	if (!window.FileReader) {
		// The file API isn't yet supported on user's browser
		return true;
	}

	input = document.getElementById(id);
	if (!input) {
		// couldn't find the file input element
		return true;
	}
	else if (!input.files) {
		// browser doesn't seem to support the `files` property of file inputs
		return true;
	}
	else if (!input.files[0]) {
		// no file has been selected for the upload
		alert( "<?php echo $error_select_file; ?>" );
		return false;
	}
	else {
		file = input.files[0];
		file_size = file.size;
		<?php if (!empty($post_max_size)) { ?>
		// check against PHP's post_max_size
		post_max_size = <?php echo $post_max_size; ?>;
		if (file_size > post_max_size) {
			alert( "<?php echo $error_post_max_size; ?>" );
			return false;
		}
		<?php } ?>
		<?php if (!empty($upload_max_filesize)) { ?>
		// check against PHP's upload_max_filesize
		upload_max_filesize = <?php echo $upload_max_filesize; ?>;
		if (file_size > upload_max_filesize) {
			alert( "<?php echo $error_upload_max_filesize; ?>" );
			return false;
		}
		<?php } ?>
		return true;
	}
}

function uploadData() {

	if (!$('[name="use_saved_file"]:checked').val() && checkFileSize('upload')) {
		$('#import').submit();
	} else {
		$('#import').submit();
	}
}

function isNumber(txt){ 
	var regExp=/^[\d]{1,}$/;
	return regExp.test(txt); 
}

function validateExportForm(id) {
	var export_type = $('input[name=export_type]:checked').val();
	if ((export_type!='c') && (export_type!='p') && (export_type!='u')) {
		return true;
	}

	var val = $("input[name=range_type]:checked").val();
	var min = $("input[name=min]").val();
	var max = $("input[name=max]").val();

	if ((min=='') && (max=='')) {
		return true;
	}

	if (!isNumber(min) || !isNumber(max)) {
		alert("<?php echo $error_param_not_number; ?>");
		return false;
	}

	var count_item;
	switch (export_type) {
		case 'p': count_item = <?php echo $count_product-1; ?>;  break;
		case 'c': count_item = <?php echo $count_category-1; ?>; break;
		default:  count_item = <?php echo $count_customer-1; ?>; break;
	}
	var batchNo = parseInt(count_item/parseInt(min))+1; // Maximum number of item-batches, namely, item number/min, and then rounded up (that is, integer plus 1)
	var minItemId;
	switch (export_type) {
		case 'p': minItemId = parseInt( <?php echo $min_product_id; ?> );  break;
		case 'c': minItemId = parseInt( <?php echo $min_category_id; ?> ); break;
		default:  minItemId = parseInt( <?php echo $min_customer_id; ?> ); break;
	
	}
	var maxItemId;
	switch (export_type) {
		case 'p': maxItemId = parseInt( <?php echo $max_product_id; ?> );  break;
		case 'c': maxItemId = parseInt( <?php echo $max_category_id; ?> ); break;
		default:  maxItemId = parseInt( <?php echo $max_customer_id; ?> ); break;

	}

	if (val=="page") {  // Min for the batch size, Max for the batch number
		if (parseInt(max) <= 0) {
			alert("<?php echo $error_batch_number; ?>");
			return false;
		}
		if (parseInt(max) > batchNo) {        
			alert("<?php echo $error_page_no_data; ?>"); 
			return false;
		} else {
			$("input[name=max]").val(parseInt(max)+1);
		}
	} else {
		if (minItemId <= 0) {
			alert("<?php echo $error_min_item_id; ?>");
			return false;
		}
		if (parseInt(min) > maxItemId || parseInt(max) < minItemId || parseInt(min) > parseInt(max)) {  
			alert("<?php echo $error_id_no_data; ?>"); 
			return false;
		}
	}
	return true;
}



//--></script>

</div>
<?php echo $footer; ?>
