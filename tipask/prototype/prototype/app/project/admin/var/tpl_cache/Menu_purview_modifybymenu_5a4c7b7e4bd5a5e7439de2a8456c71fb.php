<?php include Base_Common::tpl('contentHeader'); ?>
<fieldset><legend> <?php echo $menu['name']; ?> 权限</legend>

<form action="?ctl=menu/permission&ac=update.by.menu" method="post">
<input type="hidden" name="menu_id" value="<?php echo $menu_id; ?>" />
<table class="table table-bordered table-striped" width="100%">
<tr><th>用户组</th>
<?php if (is_array($permission_list)) { foreach ($permission_list as $permission_name => $permission) { ?>
<th><?php echo $permission_name; ?></th>
<?php } } ?>
</tr>
<?php if (is_array($group)) { foreach ($group as $row) { ?>
<tr class="hover">
<td><?php echo $row['name']; ?></td>
<?php if (is_array($row['permission_list'])) { foreach ($row['permission_list'] as $p => $pn) { ?>
<td><input type="checkbox" name="permission[<?php echo $row['group_id']; ?>][<?php echo $p; ?>]" value="1" <?php if($pn == 1) { ?>checked<?php } ?> /></td>
<?php } } ?>
</tr>
<?php } } ?>

<tr class="noborder"><td colspan="22">
<button type="submit">修改</button>
</td></tr>
</table>
</form>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
