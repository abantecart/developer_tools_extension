<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<div class="contentBox">
  <div class="cbox_tl"><div class="cbox_tr"><div class="cbox_tc">
	<div class="heading icon_information"><?php echo $heading_title; ?></div>
	<div class="heading-tabs">
					<?php
					foreach($tabs as $tab){
						echo '<a '.($tab['active']? 'class="active"' : '').' href="'.$tab['href'].'"><span>'.$tab['text'].'</span></a>';
					}
					?>
	</div>
  </div></div></div>
  <div class="cbox_cl"><div class="cbox_cr"><div class="cbox_cc">

		<div class="fieldset">
		  <div class="heading"><?php echo $text_recent ?></div>
		  <div class="top_left"><div class="top_right"><div class="top_mid"></div></div></div>
		  <div class="cont_left"><div class="cont_right"><div class="cont_mid">
			<table>
			<?php foreach ((array)$projects as $project) { ?>
			<tr style="text-align: center;">
				<td style="min-width: 300px;"><?php echo $project['id'] ?></td>
				<td style="min-width: 100px;"><?php echo $project['update_date']; ?></td>
				<td style="min-width: 200px;"><?php echo $project['button'] ?></td>
			</tr>
			<?php }  ?>
		  </table>
		  </div></div></div>
		  <div class="bottom_left"><div class="bottom_right"><div class="bottom_mid"></div></div></div>
		</div>
  </div></div></div>
  <div class="cbox_bl"><div class="cbox_br"><div class="cbox_bc"></div></div></div>
</div>
