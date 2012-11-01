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
					<?php foreach ($form[ 'fields' ] as $section => $langs) { ?>
						<div class="fieldset">
							<div class="heading"><?php echo ${'tab_' . $section . '_languages_section'}; ?></div>
							<div class="top_left">
								<div class="top_right">
									<div class="top_mid"></div>
								</div>
							</div>
							<div class="cont_left">
								<div class="cont_right">
									<div class="cont_mid">
										<table class="form list">
											<?php foreach ($langs as $lang => $fields) { ?>
										<tr id="tr_<?php echo $name; ?>" ><td ><div style="font: normal 18px Arial, Helvetica, sans-serif;
										    color: #11558f; display: inline-block; background: #fff;"><?php echo $lang; ?></div></td><td></td></tr>
											<?php
											$k=1;
											foreach ($fields as $field) { ?>
												<tr id="tr_<?php echo $name; ?>">
													<td><?php echo $k.'. '.$field[ 'text' ]; ?></td>
													<td class="ml_field">
														<a href="<?php echo $field[ 'href' ]; ?>" title="edit">
															<img src="<?php echo $template_dir; ?>image/icons/icon_grid_edit.png"></a>
													</td>
												</tr>
												<?php
											$k++; }
										}      ?>
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
						<?php } ?>

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
