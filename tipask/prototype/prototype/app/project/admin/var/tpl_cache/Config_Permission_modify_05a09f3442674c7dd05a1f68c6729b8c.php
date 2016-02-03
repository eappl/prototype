<?php include Base_Common::tpl('contentHeader'); ?>
<form name="group_update_form" id="group_update_form" action="?ctl=config/permission&ac=permission.modify" method="post">
<table class="tbv" width="100%">
<INPUT TYPE="hidden" NAME="group_id" id="group_id" value="<?php echo $group_id; ?>">
<fieldset>
<legend>全局</legend>
	<?php if (is_array($totalPermission['total'])) { foreach ($totalPermission['total'] as $AreaId => $area_data) { ?> <?php echo $area_data['name']; ?> <?php if (is_array($area_data['partner_type'])) { foreach ($area_data['partner_type'] as $partner_type => $partner_type_data) { ?> 
			<input type = 'checkbox' name = 'total_default_permission[]' <?php if($partner_type_data['permission']==1) { ?> checked <?php } ?> value="<?php echo $AreaId; ?>_<?php echo $partner_type; ?>"><?php echo $partner_type_data['name']; ?>
  	<?php } } ?> 	<?php } } ?>
</fieldset>

	<?php if (is_array($totalPermission['list'])) { foreach ($totalPermission['list'] as $AppId => $app_data) { ?>
	<fieldset> 
<legend>
<?php echo $app_data['name']; ?>
	<?php if (is_array($app_data['default'])) { foreach ($app_data['default'] as $AreaId => $area_data) { ?> <?php echo $area_data['name']; ?> <?php if (is_array($area_data['partner_type'])) { foreach ($area_data['partner_type'] as $partner_type => $partner_type_data) { ?> 
			<input type = 'checkbox' name = 'default_permission[]' <?php if($partner_type_data['permission']==1) { ?> checked <?php } ?> value="<?php echo $AppId; ?>_<?php echo $AreaId; ?>_<?php echo $partner_type; ?>"><?php echo $partner_type_data['name']; ?>
  	<?php } } ?> 	<?php } } ?>

		
</legend>

		<?php if (is_array($app_data['partner'])) { foreach ($app_data['partner'] as $PartnerId => $partner_data) { ?>
		<input type = 'checkbox' name = 'PartnerIds[]' <?php if($partner_data['permission']==1) { ?> checked <?php } ?> value="<?php echo $AppId; ?>_<?php echo $partner_data['PartnerId']; ?>"><?php echo $partner_data['name']; ?>
		<?php } } ?>
		
</fieldset>
	
	<?php } } ?>


	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="group_update_submit">提交</button></td><td>&nbsp;</td>
	</tr>
</table>
</form>
<?php include Base_Common::tpl('contentFooter'); ?>
