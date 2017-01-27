<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php if ($dt_attention) { ?>
	<div class="info alert alert-warning"><i class="fa fa fa-exclamation-triangle fa-fw"></i> <?php echo $dt_attention; ?></div>
<?php } ?>
<?php echo $project_summary ?>
<?php echo $dev_tabs ?>

<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">

		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<div class="col-md-12 mb10">
			<label class="h4 heading"><?php echo $developer_tools_text_get_start ?>...</label>
			<br>
				<dl class="dl-horizontal col-sm-5 col-sm-offset-4">
					<?php
					if($actions){
						foreach($actions as $action){ ?>
							<dt><i class="fa fa-<?php echo $action['icon'] ?>"></i></dt>
							<dd class="list-unstyled">
								<a class="" href="<?php echo $action['href'] ?>"> <?php echo $action['text'] ?></a>
							</dd>
					<?php }
					} ?>
				</dl>
		</div>
	</div>
</div>