<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<div class="contentBox">

	<div class="fieldset">
		<div class="heading"><?php echo $text_general; ?></div>
		<div class="top_left">
			<div class="top_right">
				<div class="top_mid"></div>
			</div>
		</div>
		<div class="cont_left">
			<div class="cont_right">
				<div class="cont_mid">

					<?php echo $form[ 'form_open' ]; ?>
					<div class="fieldset">
						<div class="heading"><?php echo $text_layout_xml_tab; ?></div>
						<div class="top_left">
							<div class="top_right">
								<div class="top_mid"></div>
							</div>
						</div>
						<div class="cont_left">
							<div class="cont_right">
								<div class="cont_mid">
									<table class="form list">
										<tr style="white-space: nowrap;">
											<td ><?php echo $filepath; ?></td>
											<td >
												<?php if ($text_view_layout_xml) { ?>
												<a title="<?php echo $text_view_layout_xml?>"

												   class="btn_action btn_grid"
												   id="view_layout_xml">
													<img border="0" alt="view"
														 src="admin/view/default/image/icons/icon_grid_view.png">
												</a>
												<?php } ?>
											</td>
										</tr>
										<tr style="white-space: nowrap;">
											<td>
												<?php echo $text_select_template . $form[ 'template' ]; ?>
												<a class="btn_standard" id="save_xml" href="Javascript:void(0);"><?php echo $form[ 'submit' ]; ?></a>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
						<div class="bottom_left">
							<div class="bottom_right">
								<div class="bottom_mid"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="bottom_left">
			<div class="bottom_right">
				<div class="bottom_mid"></div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$('#save_xml').click(function(){
		$.ajax({
			url:'<?php echo $xml_build_url;?>',
			type:'POST',
			dataType:'json',
			data:{template_id:$('#extLayoutFrm_template_id').val()},
			success:function (json) {
				if(json.error){
					$('.form.list').before('<div id="result_message" class="warning">'+json.message+'</div>');
				}else{
					$('.form.list').before('<div id="result_message" class="success">'+json.message+'</div>');
				}
			}
		});
	return false;
	});

	$('#view_layout_xml').click(function(){
		window.open('<?php echo $relative_path; ?>', "layout_xml", "width=800,height=600,scrollbars=yes");
		event.preventDefault();
	});
</script>
