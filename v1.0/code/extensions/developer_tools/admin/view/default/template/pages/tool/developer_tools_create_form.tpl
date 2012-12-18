<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<div class="contentBox">
  <div class="cbox_tl"><div class="cbox_tr"><div class="cbox_tc">
    <div class="heading icon_information"><?php echo $heading_title; ?></div>
	<div class="heading-tabs">
					<?php
					foreach($tabs as $tab){
						echo '<a '.($tab['active']? 'class="active"' : '').' href="'.$tab['href'].'"><span>'.$tab['text'].'</span></a>';
					}
					?>
	</div>
  </div></div></div>
  <div class="cbox_cl"><div class="cbox_cr"><div class="cbox_cc">
	  <h4><?php echo $text_about_creation ?></h4>
    <?php echo $form['form_open']; ?>
		<?php foreach ($form['fields'] as $section => $fields) { ?>
        <div class="fieldset">
          <div class="heading"><?php echo ${'tab_'.$section.'_section'}; ?></div>
          <div class="top_left"><div class="top_right"><div class="top_mid"></div></div></div>
          <div class="cont_left"><div class="cont_right"><div class="cont_mid">
            <table class="form">
            <?php foreach ($fields as $name => $field) { ?>
			<tr id="tr_<?php echo $name; ?>">
				<td><?php echo $field['text']; ?></td>
				<td class="ml_field">
					<?php echo $field['field']; ?>
				</td>
			</tr>
            <?php }  ?>
          </table>
	      </div></div></div>
          <div class="bottom_left"><div class="bottom_right"><div class="bottom_mid"></div></div></div>
	    </div>
		<?php }  ?>

	<div class="buttons align_center">
	  <button type="submit" class="btn_standard"><?php echo $form['submit']; ?></button>
	  <a class="btn_standard" href="<?php echo $cancel; ?>" ><?php echo $form['cancel']; ?></a>
    </div>
	</form>
  </div></div></div>
  <div class="cbox_bl"><div class="cbox_br"><div class="cbox_bc"></div></div></div>
</div>
<script language="JavaScript">
	function clone_and_push(id){
			$('#'+id).parents('.afield').last().after($('#'+id).parents('.afield').last().clone()).after('<br>');
	}

	$('#add_cartversions').click(function(){
		clone_and_push('extFrm_cartversions\\\[\\\]');
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
		$('#extFrm_admin_page_controller_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_response_controller_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_model_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_view_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});

		$('#extFrm_admin_page_view_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_response_view_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});

		// storefront
		$('#extFrm_storefront_page_controller_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_response_controller_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_model_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_view_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});

		$('#extFrm_storefront_page_view_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_response_view_routes\\\[\\\]').each(function(){
			$(this).val()=='' ? $(this).val($('#extFrm_route').val()) : null;
		});
	});

	$('#add_admin_page_controllers').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_admin_response_controllers').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_admin_models').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_admin_page_views').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_admin_response_views').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_storefront_page_controllers').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_storefront_response_controllers').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_storefront_models').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_storefront_page_views').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_storefront_response_views').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	} );
	$('#add_dependency').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
	} );
	$('#add_preview').click(function(){
		var clone = $('#extFrm_preview\\\[\\\]_fileupload').last().clone();
		$(this).before(clone);
		$('#extFrm_preview\\\[\\\]').last().next().find('.atext').html('Click to browse file').addClass('default_text');
	} );

    $('select, input').change(function(){

    });

	$('#extFrm_extension_type').change(function(){
		if($(this).val()=='template' || $(this).val()=='language'){
			$('#tr_copy_default').show();
		}else if($(this).val()=='payment' || $(this).val()=='shipping'){
			$('#extFrm_storefront_model_routes\\\[\\\], ' +
			  '#extFrm_storefront_page_controller_routes\\\[\\\], ' +
			  '#extFrm_storefront_response_controller_routes\\\[\\\], ' +
			  '#extFrm_storefront_view_routes\\\[\\\], ' +
			  '#extFrm_storefront_page_view_routes\\\[\\\], ' +
			  '#extFrm_storefront_response_view_routes\\\[\\\]')
			.each(function(){
					$(this).val('extension');
			});
			$('#tr_copy_default').hide();
		}else if($(this).val()=='total'){
			$('#extFrm_storefront_model_routes\\\[\\\], ' +
						  '#extFrm_storefront_page_controller_routes\\\[\\\], ' +
						  '#extFrm_storefront_response_controller_routes\\\[\\\], ' +
					      '#extFrm_storefront_view_routes\\\[\\\], ' +
						  '#extFrm_storefront_page_view_routes\\\[\\\], ' +
						  '#extFrm_storefront_response_view_routes\\\[\\\]')
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