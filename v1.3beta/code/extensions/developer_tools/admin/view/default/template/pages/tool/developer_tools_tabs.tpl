
<ul class="nav nav-tabs nav-justified nav-profile">
	<?php foreach($tabs as $tab){ ?>
		<li <?php echo ( $tab['active'] ? 'class="active"' : '' ) ?>>
			<a href="<?php echo $tab['href']; ?>"><span><?php echo $tab['text']; ?></span></a></li>
	<?php } ?>
</ul>
