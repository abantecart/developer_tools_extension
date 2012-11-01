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
<?php echo $form['form_open']; ?>
            <table class="form">
            <?php foreach ($form['fields'] as $field) { ?>
			<tr>
				<td><?php echo $field['text']; ?></td>
				<td class="ml_field">
					<?php echo $field['field']; ?>
				</td>
			</tr>
            <?php }  ?>
          </table>

	<div class="buttons align_center">
	  <button type="submit" class="btn_standard"><?php echo $form['submit']; ?></button>
	  <a class="btn_standard" href="<?php echo $cancel; ?>" ><?php echo $form['cancel']; ?></a>
    </div>
	</form>

  </div></div></div>
  <div class="cbox_bl"><div class="cbox_br"><div class="cbox_bc"></div></div></div>
</div>
<script language="JavaScript">
		$('#add_dependencies').click(function(){
		var clone = $(this).prev().clone();
		$(this).before(clone);
	} );
	</script>