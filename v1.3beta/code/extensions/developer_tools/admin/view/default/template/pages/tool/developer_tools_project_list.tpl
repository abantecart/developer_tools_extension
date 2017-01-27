<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php if ($dt_attention) { ?>
	<div class="info alert alert-warning"><i class="fa fa fa-exclamation-triangle fa-fw"></i> <?php echo $dt_attention; ?></div>
<?php } ?>
<?php echo $project_summary ?>
<?php echo $dev_tabs ?>

<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
			<div class="btn-group mr10 toolbar">
				<a class="btn btn-primary tooltips" href="<?php echo $create_url; ?>" title="<?php echo $developer_tools_tab_generate_extension; ?>">
				<i class="fa fa-plus"></i>&nbsp;<?php echo $developer_tools_text_create_project; ?>
				</a>
			</div>
		</div>
		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<div class="col-md-12 mb10">
			<label class="h4 heading"><?php echo $developer_tools_text_recent ?></label>
				<table class="table">
					<?php
					if($projects){
						foreach($projects as $project){

							?>
							<tr>
								<td class="col-sm-3"><?php echo $project['id'] ?></td>
								<td class="col-sm-3"><?php echo $project['update_date']; ?></td>
								<td class="col-sm-2"><?php echo $project['button'] ?></td>
							</tr>
					<?php }
					}else{ ?>
						<tr>
							<td class="col-sm-12"><?php echo $developer_tools_text_no_projects; ?></td>
						</tr>
					<?php } ?>
				  </table>

		</div>
	</div>
</div>