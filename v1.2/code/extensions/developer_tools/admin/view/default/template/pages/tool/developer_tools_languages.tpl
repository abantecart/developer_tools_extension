<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $dev_tabs; ?>
<?php echo $prj_tabs ?>
<div id="content" class="panel panel-default">
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<div class="row">
		<?php foreach($form['fields'] as $section => $langs){ ?>
			<div class="col-sm-6">
			<label class="h4 heading" id="<?php echo $section; ?>"><?php echo ${'developer_tools_tab_' . $section . '_languages_section'}; ?></label>
			<?php foreach($langs as $lang => $fields){ ?>
				<div class="form-group">
				<?php
					foreach($fields as $field){ ?>
						<label class="control-label col-sm-10 col-xs-12"><?php echo $field['text']; ?></label>
						<div class="input-group afield mb10">
							<a class="btn btn-default" href="<?php echo $field['href']; ?>" title="edit">
								<i class="fa fa-pencil"></i> <?php echo $text_edit;?></a>
						</div>
					<?php } ?>
				</div>

			<?php } ?>
			</div>
		<?php } ?>
		</div>
	</div>
</div>
