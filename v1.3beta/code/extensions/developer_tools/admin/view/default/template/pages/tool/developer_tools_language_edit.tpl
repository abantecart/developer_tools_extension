<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $dev_tabs; ?>
<?php echo $prj_tabs ?>
<div id="content" class="panel panel-default">
	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12 ">
		<label class="h4 heading" ><?php echo $heading_title; ?></label>
			<?php foreach ($form['fields'] as $name => $field) {  ?>
			<div class="form-group">
				<label class="control-label col-sm-3 col-xs-12" ><?php echo $name?></label>
				<div class="input-group afield col-sm-7 col-xs-12">
					<?php
					//when a few fields in row
					if(is_array($field)){ ?>
						<div class="grouped input-group afield col-sm-10">
					<?php foreach($field as $i=>$subfld){
								if(is_object($subfld) ){
									echo $subfld;
								}else{
									echo '<div class="input-group">'.$subfld.'</div>';
								}	?>
							<?php } ?>
						</div>
				<?php }?>
				</div>
			</div>
				<?php }  ?><!-- <div class="fieldset"> -->

		<label class="h4 heading" ><?php echo $developer_tools_add_definition; ?></label>
			<div class="form-group new">
				<label class="control-label col-sm-3 col-xs-12" ><?php echo $form['proto']['newkey']; ?></label>
				<div class="input-group afield col-sm-7 col-xs-12">
					<?php
					//when a few fields in row
					if(is_array($field)){ ?>
						<div class="grouped input-group afield col-sm-10">
					<?php foreach($form['proto'][ 'field' ] as $language_name=>$subfld){
								if(is_object($subfld) ){
									echo $subfld;
								}else{
									echo '<div class="input-group">'.$subfld.'</div>';
								}	?>
							<?php } ?>
						</div>
				<?php }?>
				</div>
			</div>

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


<?php /*?>

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
<?php */?>
<script language="JavaScript">

	$('input[name="newkey"]').keyup(function(){
		var that = $(this);
		that.val(that.val().replace(/[^A-z0-9_]/g,''));
		$(this).parents('td').next().find('textarea').each( function(){
			$(this).attr('name', $(this).attr('basename')+'['+ that.val()+']');
			$(this).change();
		});

	});

	$('#extLanguageFrm').bind('submit',function(){
		if($('#extLanguageFrm_newkey').val()==''){
			return;
		}
		$("textarea[name$='[]']").each(function(){
			$(this).attr('name', $(this).attr('name').replace('[]','['+$('#extLanguageFrm_newkey').val()+']'));
		});
	});




</script>
