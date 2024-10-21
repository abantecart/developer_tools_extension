<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $dev_tabs ?>

<div id="content" class="panel panel-default">
	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
			<?php foreach ($form['fields'] as $section => $fields) {		?>
			<label class="h4 heading" id="<?php echo $section;?>"><?php echo ${'developer_tools_tab_' . $section.'_section'}; ?></label>
				<?php foreach ($fields as $name => $field) {

				if($field->type=='hidden'){
					echo $field;
					continue;
				}

					//Logic to calculate fields width
					$widthcasses = "col-sm-3 col-xs-12";
				 ?>
			<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
				<label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'developer_tools_entry_' . $name}; ?></label>
				<div id="<?php echo $name?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
					<?php
						echo $field;
					?>
				</div>
			    <?php if (!empty($error[$name])) { ?>
			    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
			    <?php } ?>
			</div>
				<?php }}  ?><!-- <div class="fieldset"> -->

	</div>

	<div class="panel-footer col-xs-12">
		<div class="text-center">
			<button class="btn btn-primary lock-on-click">
			<i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
			</button>
			<a class="btn btn-default" href="<?php echo $cancel; ?>">
			<i class="fa fa-arrow-left fa-fw"></i> <?php echo $form['cancel']->text; ?>
			</a>
		</div>
	</div>
	</form>

</div>





<script type="application/javascript">

	$('#extFrm_extension_txt_id').keyup(function(){
		var that = $(this);
		that.val(that.val().replace(/[^A-z0-9_]/g,''));
	});



	$('#extFrm_extension_type').change(function(){
		if($(this).val()=='template' || $(this).val()=='language'){
			$('#tr_copy_default').show();
		}else if($(this).val()=='payment' || $(this).val()=='shipping'){
			$('#extFrm_storefront_model_routes, ' +
			  '#extFrm_storefront_page_controller_routes, ' +
			  '#extFrm_storefront_response_controller_routes, ' +
			  '#extFrm_storefront_view_routes, ' +
			  '#extFrm_storefront_page_view_routes, ' +
			  '#extFrm_storefront_response_view_routes')
			.each(function(){
					$(this).val('extension');
			});
			$('#tr_copy_default').hide();
		}else if($(this).val()=='total'){
			$('#extFrm_storefront_model_routes, ' +
						  '#extFrm_storefront_page_controller_routes, ' +
						  '#extFrm_storefront_response_controller_routes, ' +
					      '#extFrm_storefront_view_routes, ' +
						  '#extFrm_storefront_page_view_routes, ' +
						  '#extFrm_storefront_response_view_routes')
			.each(function(){
					$(this).val('total');
			});
			$('#tr_copy_default').hide();
		}
		else{
			$('#tr_copy_default').hide();
		}
    });


</script>

