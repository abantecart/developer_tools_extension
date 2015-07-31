<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php if ($dt_attention) { ?>
	<div class="info alert alert-warning">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
		<i class="fa fa fa-exclamation-triangle fa-fw"></i> <?php echo $dt_attention; ?>
	</div>
<?php } ?>
<?php echo $project_summary ?>
<?php echo $dev_tabs ?>
<?php echo $prj_tabs ?>

<div id="content" class="panel panel-default">
	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
			<?php foreach ($form['fields'] as $section => $fields) { ?>
			<label class="h4 heading" id="<?php echo $section;?>"><?php echo ${'developer_tools_tab_' . $section.'_section'}; ?></label>
				<?php foreach ($fields as $name => $field) {

				if(is_array($field)){
					$widthcasses = "col-sm-7 col-xs-12";
				}else{
					//Logic to calculate fields width
					$widthcasses = "col-sm-7";
					if(is_int(stripos($field->style, 'large-field'))){
						$widthcasses = "col-sm-7";
					} else if(is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date'))){
						$widthcasses = "col-sm-5";
					} else if(is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch'))){
						$widthcasses = "col-sm-3";
					} else if(is_int(stripos($field->style, 'tiny-field'))){
						$widthcasses = "col-sm-2";
					}
					$widthcasses .= " col-xs-12";
				} ?>
			<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
				<label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'developer_tools_entry_' . $name}; ?></label>
				<div id="<?php echo $name?>" class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
					<?php
					//when a few fields in row
					if(is_array($field)){ ?>
					<?php foreach($field as $subfldgroup){ ?>
								<div class="grouped input-group afield col-sm-12">
								<?php
								if(is_array($subfldgroup)){
									foreach($subfldgroup as $i => $subfld){
										if(is_object($subfld) && !$subfld->required){
											$subfld->attr = $subfld->attr . ' style="width: auto; float:none;margin-bottom: 10px;"';
										}
										echo $subfld;
									}
								}else{
									echo $subfldgroup;
								}
								 ?>
								</div>
						<?php }
					}else{
						echo $field;
					}?>
				</div>
			    <?php if (!empty($error[$name])) { ?>
			    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
			    <?php } ?>
			</div>
				<?php }  ?><!-- <div class="fieldset"> -->
			<?php }  ?>
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
	function clone_and_push(id){
		var dd = $('#'+id).find('.grouped').last().prev();
		var clone = dd.clone();
		$('#'+id).find('.grouped').last().before(clone);
	}

	$(document).on('click','.btn-success', function(){
		var id = $(this).attr('id').replace('add_','');
		clone_and_push(id);
		return false;
	} );
	// fill default rt
	$('#extFrm_extension_txt_id').blur(function(){
		$('#extFrm_route').val()=='' ? $('#extFrm_route').val($('#extFrm_extension_txt_id').val()) : null;
		if($('#extFrm_hook_file').val()=='' && $('#extFrm_extension_type').val()== 'template' ){
			$('#extFrm_hook_file').val($('#extFrm_extension_txt_id').val()+'_hooks.php');
		}
	} );

	$('#extFrm_route').blur(function(){
		// admin
		$('#extFrm_admin_page_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_response_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_api_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_task_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_model_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_view_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});

		$('#extFrm_admin_page_view_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_response_view_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});

		// storefront
		$('#extFrm_storefront_page_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_response_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_api_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_block_controller_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_model_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_view_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});

		$('#extFrm_storefront_page_view_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_response_view_routes').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
	});

	$('#extFrm_icon_default').click(function(){
		var upl = $(this).nextUntil($('.file-input')).next();
		if($(this).attr('checked')){
			upl.fadeOut();
		}else{
			upl.fadeIn();
		}
	} );

	$(document).ready(function(){
		var upl = $('#extFrm_icon_default').nextUntil($('.file-input')).next();
		$('#extFrm_icon_default').attr('checked') ? upl.fadeOut() : upl.fadeIn();
	});

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

