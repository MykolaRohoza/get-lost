<thead>
	<tr>
		<?php foreach ($keys as $key) { ?>
			<td class="text-left"><?=${'text_' . $key}; ?></td>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php $product_row = 0; ?>
	<?php foreach ($products as $product) { ?>
			<tr>
				<?php foreach ($keys as $key) {
					if($key !== 'php_id'){?>
					<td class="text-left"><?=$product[$key]; ?></td>
					<?php } else { ?>
					<td class="text-left"><input type="checkbox" 
												 <?=((empty($is_search))? 'checked="checked"': '')?>
												 value="<?=$product[$key]; ?>"
												 name="linked_products[]"
												 ></td>
					<?php } ?>
				<?php } ?>
			</tr>
			<?php $product_row++; ?>
		<?php } ?>
</tbody>
