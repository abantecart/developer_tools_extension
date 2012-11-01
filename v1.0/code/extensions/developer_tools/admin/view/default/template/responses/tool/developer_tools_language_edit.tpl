<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<div class="contentBox">
	<?php echo $form[ 'form_open' ]; ?>

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
						<div class="fieldset">
							<div class="heading"><?php echo $heading; ?></div>
							<div class="top_left">
								<div class="top_right">
									<div class="top_mid"></div>
								</div>
							</div>
							<div class="cont_left">
								<div class="cont_right">
									<div class="cont_mid">
										<table class="form list">
											<?php
											$k=1;
											foreach ($form['fields'] as $key => $field) { ?>
												<tr >
													<td><?php echo $k.'. '.$field[ 'text' ]; ?></td>
													<td class="ml_field">
														<?php
														foreach($field[ 'field' ] as $language_name=>$fld){ ?>
															<div style="padding: 5px">
																<div style="width: 170px"><?php echo $language_name.':';?></div>
																<?php echo $fld; ?>
															</div>
														<?php }
														 ?>
													</td>
												</tr>
												<?php
											$k++; }
										     ?>

											<tr id="proto">
												<td><?php echo $k.'. '.$form['proto'][ 'text' ]; ?></td>
												<td class="ml_field">
													<?php
													foreach($form['proto'][ 'field' ] as $language_name=>$fld){ ?>
														<div style="padding: 5px">
															<div style="width: 170px"><?php echo $language_name.':';?></div>
															<?php echo $fld; ?>
														</div>
													<?php }
													 ?>
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
						</div><!-- <div class="fieldset"> -->


				</div>
			</div>
		</div>
		<div class="bottom_left">
			<div class="bottom_right">
				<div class="bottom_mid"></div>
			</div>
		</div>
	</div>


	<div class="buttons align_center">
		<button type="submit" class="btn_standard"><?php echo $form[ 'submit' ]; ?></button>
		<a class="btn_standard" href="<?php echo $cancel; ?>"><?php echo $form[ 'cancel' ]; ?></a>
	</div>

	</form>
</div>

<script language="JavaScript">

	$('input[name="newkey"]').keyup(function(){
		var that = $(this);
		that.val(that.val().replace(/[^A-z0-9_]/g,''));
		$(this).parents('td').next().find('textarea').each( function(){
			$(this).attr('name', $(this).attr('basename')+'['+ that.val()+']');
			$(this).change();
		});

	});

	$('input[name="newkey"]')

//var clone = $('#proto').clone();


</script>
