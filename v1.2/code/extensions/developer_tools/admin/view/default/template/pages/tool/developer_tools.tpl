<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $dev_tabs ?>
<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<div class="col-md-9 mb10">
			<label class="h4 heading"><?php echo $developer_tools_text_recent ?></label>
				<table class="table">
					<?php
					if($projects){
						foreach($projects as $project){ ?>
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