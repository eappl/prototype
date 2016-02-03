<?php include Base_Common::tpl('contentHeader'); ?>
<fieldset><legend> <?php echo $group['name']; ?> 组权限</legend>


<form action="?ctl=menu/permission&ac=update.by.group" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<table class="table table-bordered table-striped" width="100%">
<tr><th>菜单</th>
<th>权限</th></tr>
<?php if (is_array($menu)) { foreach ($menu as $row) { ?>
<tr class="hover">
<td><label class="checkbox"><?php echo $row['prefix']; ?><?php echo $row['name']; ?></label></td>
<td>
<?php if (is_array($row['permission_detail'])) { foreach ($row['permission_detail'] as $p => $p_info) { ?>
<p>
<input type="checkbox"  name="permission[<?php echo $row['menu_id']; ?>][<?php echo $p; ?>]" value="1" <?php if($p_info['selected'] == 1) { ?>checked<?php } ?> /> <?php echo $p_info['permission_name']; ?> 
<?php } } ?>
</td>
</tr>
<?php } } ?>

<tr class="noborder"><td colspan="22">
<button type="submit" class="btn btn-info btn-small">修改</button>
</td></tr>
</table>
</form>
</fieldset>
<script type="text/javascript">

</script>
<?php include Base_Common::tpl('contentFooter'); ?>