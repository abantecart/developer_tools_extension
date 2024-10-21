<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<div class="contentBox">
    <?php echo $form['form_open']; ?>

        <div class="fieldset">
          <div class="heading"><?php echo $text_general; ?></div>
          <div class="top_left"><div class="top_right"><div class="top_mid"></div></div></div>
          <div class="cont_left"><div class="cont_right"><div class="cont_mid">

            <table class="form">

				<tr><td align="center" valign="middle" style="height: 400px;"><h1>functionality will be available in the future</h1></td></tr>

            <?php foreach ((array)$form['fields'] as $name => $field) { ?>
			<tr id="tr_<?php echo $name; ?>">
				<td><?php echo $field['text']; ?></td>
				<td class="ml_field">
					<?php echo $field['field']; ?>
				</td>
			</tr>
            <?php }  ?>
          </table>

	      </div></div></div>
          <div class="bottom_left"><div class="bottom_right"><div class="bottom_mid"></div></div></div>
	    </div>


	<div class="buttons align_center">
	  <button type="submit" class="btn_standard"><?php echo $form['submit']; ?></button>
	  <a class="btn_standard" href="<?php echo $cancel; ?>" ><?php echo $form['cancel']; ?></a>
    </div>
	</form>


</div>
