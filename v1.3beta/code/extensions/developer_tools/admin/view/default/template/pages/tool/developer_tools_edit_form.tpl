<?php

include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php if ($dt_attention){ ?>
	<div class="info alert alert-warning">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<i class="fa fa fa-exclamation-triangle fa-fw"></i> <?php echo $dt_attention; ?>
	</div>
<?php } ?>
<?php echo $project_summary ?>
<?php echo $dev_tabs ?>
<?php echo $prj_tabs;



?>

<div id="content" class="panel panel-default">
	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<?php foreach (array('common', 'language_extension_settings') as $section){?>
			<div id="section_<?php echo $section;?>">
			<label class="h4 heading"
			       id="<?php echo $section; ?>"><?php echo ${'developer_tools_tab_' . $section . '_section'}; ?></label>
			<?php
			foreach ($form['fields'][$section] as $name => $field){

				if (is_array($field)){
					$widthcasses = "col-sm-7 col-xs-12";
				} else{
					//Logic to calculate fields width
					$widthcasses = "col-sm-7";
					if (is_int(stripos($field->style, 'large-field'))){
						$widthcasses = "col-sm-7";
					} else if (is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date'))){
						$widthcasses = "col-sm-5";
					} else if (is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch'))){
						$widthcasses = "col-sm-3";
					} else if (is_int(stripos($field->style, 'tiny-field'))){
						$widthcasses = "col-sm-2";
					}
					$widthcasses .= " col-xs-12";
				} ?>
				<div class="form-group <?php if (!empty($error[$name])){
					echo "has-error";
				} ?>">
					<label class="control-label col-sm-3 col-xs-12"
					       for="<?php echo $field->element_id; ?>"><?php echo ${'developer_tools_entry_' . $name}; ?></label>
					<div id="<?php echo $name ?>"
					     class="input-group afield <?php echo $widthcasses; ?> <?php echo($name == 'description' ? 'ml_ckeditor' : '') ?>">
						<?php
						//when a few fields in row
						if (is_array($field)){ ?>
							<?php foreach ($field as $subfldgroup){ ?>
								<div class="grouped input-group afield col-sm-12">
									<?php
									if (is_array($subfldgroup)){
										foreach ($subfldgroup as $i => $subfld){
											if (is_object($subfld) && !$subfld->required){
												$subfld->attr = $subfld->attr . ' style="width: auto; float:none;margin-bottom: 10px;"';
											}
											echo $subfld;
										}
									} else{
										echo $subfldgroup;
									}
									?>
								</div>
							<?php }
						} else{
							echo $field;
						} ?>
					</div>
					<?php if (!empty($error[$name])){ ?>
						<span class="help-block field_err"><?php echo $error[$name]; ?></span>
					<?php }

					if($name == 'translation_method'){ ?>
						<div class="text-center col-xs-12 mt10">
						  <button class="btn btn-primary task_run"
						        data-run-task-url="<?php echo $build_task_url_language; ?>"
						        data-complete-task-url="<?php echo $complete_task_url_language; ?>">
						  <i class="fa fa-save"></i> <?php echo $developer_tools_translate_text; ?>
						  </button>
						</div>
					<?php } ?>
				</div>
			<?php }
			unset($form['fields'][$section]);

			if($form['language_extension_settings']['note'] && $section == 'language_extension_settings'){?>
				<div class="text-center col-xs-12">
					<div class="info alert alert-info"><i class="fa fa fa-check fa-fw"></i> <?php echo $form['language_extension_settings']['note']; ?></div>
				</div>
			<?php }	?>
			</div>
		<?php }
		if(sizeof($form['fields'])){ ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label class="h4 heading"
				       id="advanced"
				       role="button"
				       data-toggle="collapse"
				       data-parent="#accordion"
				       href="#collapse_form"><?php echo 'Advanced'; ?></label>
			</div>
			<div class="panel-body panel-body-nopadding table-responsive">
				<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
			<?php
					foreach ($form['fields'] as $section => $fields){ ?>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="heading<?php echo $section ?>">
								<label class="h5 heading "
								       role="button"
								       data-toggle="collapse"
								       data-parent="#accordion"
									   href="#collapse<?php echo $section ?>"
									   aria-expanded="true"
									   aria-controls="collapse<?php echo $section ?>">
										<?php echo ${'developer_tools_tab_' . $section . '_section'}; ?>
								</label>
							</div>
							<div id="collapse<?php echo $section ?>"
							     class="panel-collapse collapse"
							     role="tabpanel"
							     aria-labelledby="heading<?php echo $section ?>">
								<div class="panel-body">
									<?php foreach ($fields as $name => $field){
										if (is_array($field)){
											$widthcasses = "col-sm-7 col-xs-12";
										} else{
											//Logic to calculate fields width
											$widthcasses = "col-sm-7";
											if (is_int(stripos($field->style, 'large-field'))){
												$widthcasses = "col-sm-7";
											} else if (is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date'))){
												$widthcasses = "col-sm-5";
											} else if (is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch'))){
												$widthcasses = "col-sm-3";
											} else if (is_int(stripos($field->style, 'tiny-field'))){
												$widthcasses = "col-sm-2";
											}
											$widthcasses .= " col-xs-12";
										} ?>
										<div class="form-group <?php if (!empty($error[$name])){
											echo "has-error";
										} ?>">
											<label class="control-label col-sm-3 col-xs-12"
											       for="<?php echo $field->element_id; ?>"><?php echo ${'developer_tools_entry_' . $name}; ?></label>
											<div id="<?php echo $name ?>"
											     class="input-group afield <?php echo $widthcasses; ?> <?php echo($name == 'description' ? 'ml_ckeditor' : '') ?>">
												<?php
												//when a few fields in row
												if (is_array($field)){ ?>
													<?php foreach ($field as $subfldgroup){ ?>
														<div class="grouped input-group afield col-sm-12">
													<?php	if (is_array($subfldgroup)){
																foreach ($subfldgroup as $i => $subfld){
																	if (is_object($subfld) && !$subfld->required){
																		$subfld->attr = $subfld->attr . ' style="width: auto; float:none;margin-bottom: 10px;"';
																	}
																	echo $subfld;
																}
															} else{
																echo $subfldgroup;
															} ?>
														</div>
													<?php }
												} else{
													echo $field;
												} ?>
											</div>
											<?php if (!empty($error[$name])){ ?>
												<span class="help-block field_err"><?php echo $error[$name]; ?></span>
											<?php } ?>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
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

	// fill extension text_id based on title
	$('#extFrm_extension_txt_id').on('focus', function () {
		if ($(this).val().length > 0) {
			return null;
		}
		var title = $('#extFrm_extension_title').val();
		if (title.length == 0) {
			return null;
		}
		title = title.toLowerCase()
				.replace(/\s/gi, '_')
				.replace(/[\u0250-\ue007]/gi, '')
				.replace(/__/gi, '_');
		$(this).val(title);
	});

	$('#extFrm_extension_txt_id').keyup(function () {
		var that = $(this);
		that.val(that.val().replace(/[^A-z0-9_]/g, ''));
	});


	function clone_and_push(id) {
		var dd = $('#' + id).find('.grouped').last().prev();
		var clone = dd.clone();
		$('#' + id).find('.grouped').last().before(clone);
	}

	// fill default rt
	$('#accordion').on('focus', 'input[name*=routes]', function(){
		if($(this).val().length>0){ return false; }
		var default_rt = $('#extFrm_route').val();
		if(default_rt.length == 0){ return false; }
		$(this).val(default_rt);
	});
	//fill name of file with hooks
	$('#extFrm_hook_file').focus(function(){
		var ext_id = $('#extFrm_extension_txt_id').val();
		if(ext_id.length==0){ return false; }
		$('#extFrm_hook_file').val(ext_id + '_hooks.php');
	});

	$(document).on('click', '.btn-success', function () {
		var id = $(this).attr('id').replace('add_', '');
		clone_and_push(id);
		return false;
	});

	$('#extFrm_icon_default').click(function () {
		var upl = $(this).nextUntil($('.file-input')).next();
		if ($(this).attr('checked')) {
			upl.fadeOut();
		} else {
			upl.fadeIn();
		}
	});

	$(document).ready(function () {
		var upl = $('#extFrm_icon_default').nextUntil($('.file-input')).next();
		$('#extFrm_icon_default').attr('checked') ? upl.fadeOut() : upl.fadeIn();
	});


	$('#extFrm_extension_type').change(function () {
		var value = $(this).val();
		//switch category
		$('#extFrm_extension_category option').each(function(){
			if( $(this).attr('value').search(new RegExp(value, "i")) >= 0 ){
				$('#extFrm_extension_category').val($(this).attr('value'));
				return false;
			}
		});


		if( value != 'language' ) {
			toggle_language_form('hide');
			$('#tr_language_extension_settings').hide();
		}
		if( value == 'language' ) {
			toggle_language_form('show');

			$('#tr_copy_default').show();
		}else if (value == 'template' ){
			$('#tr_copy_default').show();
		} else if (value == 'payment' || value == 'shipping') {
			$('#extFrm_storefront_model_routes, ' +
					'#extFrm_storefront_page_controller_routes, ' +
					'#extFrm_storefront_response_controller_routes, ' +
					'#extFrm_storefront_view_routes, ' +
					'#extFrm_storefront_page_view_routes, ' +
					'#extFrm_storefront_response_view_routes')
					.each(function () {
						$(this).val('extension');
					});
			$('#tr_copy_default').hide();
		} else if (value == 'total') {
			$('#extFrm_storefront_model_routes, ' +
					'#extFrm_storefront_page_controller_routes, ' +
					'#extFrm_storefront_response_controller_routes, ' +
					'#extFrm_storefront_view_routes, ' +
					'#extFrm_storefront_page_view_routes, ' +
					'#extFrm_storefront_response_view_routes')
			.each(function () {
				$(this).val('total');
			});
			$('#tr_copy_default').hide();
		}else {
			$('#tr_copy_default').hide();
		}
	});

	function toggle_language_form(action){
		if(action == 'show' || !action) {
			$('#section_language_extension_settings').slideDown();
		}else{
			$('#section_language_extension_settings').slideUp();
		}
	}

	$(document).ready(function(){
		$('#extFrm_extension_type').change();
	});


</script>

