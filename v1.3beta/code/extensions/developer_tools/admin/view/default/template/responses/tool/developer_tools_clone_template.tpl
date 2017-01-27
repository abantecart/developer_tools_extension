<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title"><?php echo $developer_tools_tab_clone_template; ?></h4>
  </div>

	<div class="tab-content">

	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<?php if($text_about){?>
					<div class="info alert alert-info"><i class="fa fa fa-check fa-fw"></i> <?php echo $text_about; ?></div>
				<?php } ?>
			<?php foreach ($form['fields'] as $section => $fields) {		?>

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
			<button id="clone_btn" class="btn btn-primary lock-on-click">
			<i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
			</button>
			<a class="btn btn-default" data-dismiss="modal">
			<i class="fa fa-arrow-left fa-fw"></i> <?php echo $form['cancel']->text; ?>
			</a>
		</div>
	</div>
	</form>
	</div>

</div>

</div>



<script type="application/javascript">

	$('#extFrm_extension_txt_id').keyup(function(){
		var that = $(this);
		that.val(that.val().replace(/[^A-z0-9_]/g,''));
	});

	$('#clone_btn').click(function(){
		if($('#extFrm_clone_method').val()=='' || $('#extFrm_extension_title').val()=='' || $('#extFrm_extension_txt_id').val()==''){
			error_alert('Please fill all required fields!');
			return false;
		}
	});


	$('#extFrm').submit(function () {
		doClone();
		return false;
	});


	function doClone(){
		$.ajax({
			url: '<?php echo $form['form_open']->action; ?>',
		    type: 'POST',
		    data: $('#extFrm').serializeArray(),
		    dataType: 'json',
		    success: function (data) {
				success_alert(data.result_text);
			    goTo(data['redirect_url'])
		    }
		});
	}




</script>

