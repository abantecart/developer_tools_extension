<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php echo $dev_tabs ?>
<div id="content" class="panel panel-default">
	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
			<label class="h4 heading" ><?php echo $heading_title; ?></label>
				<?php foreach ($form['fields'] as $name => $field) {

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
				<div id="<?php echo $name?>" class="input-group afield <?php echo $widthcasses; ?> ">
					<?php
					//when a few fields in row
					if(is_array($field)){ ?>
						<div class="grouped input-group afield col-sm-12 ">
					<?php foreach($field as $i=>$subfld){
								if(is_object($subfld) && !$subfld->required){
									$subfld->attr = $subfld->attr .' style="width: auto; float:none;"';
								}
								echo $subfld; ?>
							<?php } ?>
						</div>
						<?php echo $field['add_btn']; ?>
				<?php }else{
						echo $field;
					}	?>
				</div>
			    <?php if (!empty($error[$name])) { ?>
			    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
			    <?php } ?>
			</div>
				<?php }  ?><!-- <div class="fieldset"> -->
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

	$('#GenericBlockFrm_block_txt_id').keyup(function(){
		var that = $(this);
		that.val(that.val().replace(/[^A-z0-9_]/g,''));
	});
</script>