<div class="panel panel-default">
	<div class="panel-heading">
		<div class="panel-btns">
			<a class="panel-close" href="">×</a>
			<a class="minimize" href="">−</a>
		</div>
		<h4 class="panel-title"><?php echo $developer_tools_text_project_summary; ?></h4>
	</div>
	<div class="panel-body panel-body-nopadding table-responsive" style="display: block;">
		<table id="summary" class="table summary">
			<tr>
				<td class="summary_image" rowspan="2" align="center">
					<img src="<?php echo $project['icon_href']; ?>" alt="" border="0">
				</td>
				<td class="summary_label"><?php echo $developer_tools_entry_extension_title; ?></td>
				<td class="summary_value"><?php echo $project['extension_title']; ?></td>
				<td class="summary_label"><?php echo $developer_tools_text_extension_txt_id; ?></td>
				<td class="summary_value"><?php echo $project['extension_txt_id']; ?></td>
			</tr>
			<tr>
				<td class="summary_label"><?php echo $developer_tools_entry_version; ?></td>
				<td class="summary_value"><?php echo $project['version']; ?></td>
				<td class="summary_label"><?php echo $developer_tools_text_extension_category; ?></td>
				<td class="summary_value"><?php echo $project['category']; ?></td>
			</tr>
		</table>
	</div>
</div>
