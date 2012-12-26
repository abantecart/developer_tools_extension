<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<div class="contentBox">
	<div class="cbox_tl">
		<div class="cbox_tr">
			<div class="cbox_tc">
				<div class="heading icon_information"><?php echo $heading_title; ?></div>
				<div class="heading-tabs">
					<?php
					foreach ($tabs as $tab) {
						echo '<a ' . ($tab[ 'active' ] ? 'class="active"' : '') . ' href="' . $tab[ 'href' ] . '"><span>' . $tab[ 'text' ] . '</span></a>';
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="cbox_cl">
		<div class="cbox_cr">
			<div class="cbox_cc" style="overflow: hidden;">
				<div class="flt_left">
					<ul id="page_links">
						<?php
						$locks = array('General','Controllers','Models','Views','Hooks'); ////TODO temporary locked
						foreach ($menu as $item) {
							if(!in_array($item[ 'text' ],$locks)){
							echo '<li><a href="'.$item[ 'href' ].'" '.($item[ 'active' ] ? 'class="shover"' : '').'>'.$item[ 'text' ].'</a></li>';
							}else{
								echo '<li><i style="color: grey;" >'.$item[ 'text' ].'</i></li>';
							}

						} ?>
					</ul>
				</div>
				<div id="page_layout" style="min-height: 500px; overflow: hidden;">
					<div class="page_wrapper">
						<?php echo $subform; ?>

					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="cbox_bl">
		<div class="cbox_br">
			<div class="cbox_bc"></div>
		</div>
	</div>
</div>
<script language="JavaScript">
	function clone_and_push(id) {
		$('#' + id).parents('.afield').last().after($('#' + id).parents('.afield').last().clone()).after('<br>');
	}

	$('#add_cart_versions').click(function () {
		clone_and_push('extFrm_cart_versions\\\[\\\]');
	});
	// fill default rt
	$('#extFrm_extension_txt_id').blur(function () {
		$('#extFrm_route').val() == '' ? $('#extFrm_route').val($('#extFrm_extension_txt_id').val()) : null;
	});

	$('#extFrm_route').blur(function () {
		// admin
		$('#extFrm_admin_page_controller_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_response_controller_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_model_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_view_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});

		$('#extFrm_admin_page_view_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_admin_response_view_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});

		// storefront
		$('#extFrm_storefront_page_controller_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_response_controller_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_model_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_view_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});

		$('#extFrm_storefront_page_view_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
		$('#extFrm_storefront_response_view_routes\\\[\\\]').each(function () {
			$(this).val() == '' ? $(this).val($('#extFrm_route').val()) : null;
		});
	});

	$('#add_admin_page_controllers').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_admin_response_controllers').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_admin_models').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_admin_page_views').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_admin_response_views').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_storefront_page_controllers').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_storefront_response_controllers').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_storefront_models').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_storefront_page_views').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_storefront_response_views').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
		$('#extFrm_route').blur();
	});
	$('#add_dependency').click(function () {
		var clone = $(this).prev().clone();
		$(this).before(clone);
	});
	$('#add_preview').click(function () {
		var clone = $('#extFrm_preview\\\[\\\]_fileupload').last().clone();
		$(this).before(clone);
		$('#extFrm_preview\\\[\\\]').last().next().find('.atext').html('Click to browse file').addClass('default_text');
	});

	$('select, input').change(function () {

	});

	$('#extFrm_extension_type').change(function () {
		if ($(this).val() == 'template' || $(this).val() == 'language') {
			$('#tr_copy_default').show();
		} else {
			$('#tr_copy_default').hide();
		}
	});


</script>