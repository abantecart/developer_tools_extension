<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $dev_tabs ?>
<?php echo $prj_tabs ?>
<div id="content" class="panel panel-default">
	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12 ">
		<label class="h4 heading" ><?php echo $developer_tools_text_layout_xml_title; ?></label>
		<?php if ($filepath) { ?>
			<div class="form-group">
				<label class="control-label col-sm-3 col-xs-12" ><?php echo $developer_tools_layout_xml_file; ?></label>
				<div class="input-group afield col-sm-7 col-xs-12">
					<a title="<?php echo $developer_tools_text_view_layout_xml?>"
					   id="view_layout_xml"><?php echo $filepath; ?></a>
				</div>
			</div>
		<?php } ?>
			<div class="form-group">
				<label class="control-label col-sm-3 col-xs-12" ><?php echo $developer_tools_text_select_template; ?></label>
				<div class="input-group afield col-sm-7 col-xs-12">	<?php echo $form[ 'template' ]; ?></div>
			</div>



	</div>
	<div class="panel-footer col-xs-12">
			<div class="text-center">
				<a id="btn-save-xml" class="btn btn-primary lock-on-click">
				<i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
				</a>
			</div>
		</div>
	</form>

</div>




<script type="text/javascript">
	$('#btn-save-xml').click(function(){
		$.ajax({
			url:'<?php echo $xml_build_url;?>',
			type:'POST',
			dataType:'json',
			data:{template_id:$('#extLayoutFrm_template_id').val()},
			success:function (json) {
				if(json.error){
					error_alert(json.message);
				}else{
					success_alert(json.message);
				}
				goTo()
			}
		});
	return false;
	});

	$('#view_layout_xml').click(function(){
		window.open('<?php echo $relative_path; ?>', "layout_xml", "width=800,height=600,scrollbars=yes");
		event.preventDefault();
	});
</script>
