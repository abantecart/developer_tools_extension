<div class="panel-heading col-xs-12">
	<div class="primary_content_actions pull-left">
		<?php
		$icons = array(
				'edit' => 'fa-pencil',
				'languages' => 'fa-language',
				'other' => 'fa-cogs',
				'package' => 'fa-archive'
		);
		foreach ($buttons as $name=>$btn) { ?>
	    <div class="btn-group mr10 toolbar">
            <a class="btn btn-white tooltips"
               <?php echo 'href="'.$btn['href'].'"'; ?>
               data-toggle="tooltip"
               title="<?php echo $btn['text']; ?>"
               data-original-title="<?php echo $btn['text']; ?>">
            <i class="fa <?php echo $icons[$name];?>"></i></a>
	    </div>
	    <?php } ?>
	</div>
</div>