<?php include Base_Common::tpl('contentHeader'); ?>
<fieldset><legend> <?php echo $menu['name']; ?> 权限</legend>

<form action="?ctl=menu/purview&ac=update.by.menu" method="post">
<input type="hidden" name="menu_id" value="<?php echo $menu_id; ?>" />
<table class="table table-bordered table-striped" width="100%">
<tr><th>用户组</th><th>查看权限</th><th>添加权限</th><th>修改权限</th><th>删除权限</th></tr>
<?php if (is_array($group)) { foreach ($group as $row) { ?>
<tr class="hover">
<td><?php echo $row['name']; ?></td>
<td><input type="checkbox" name="purview[<?php echo $row['group_id']; ?>][select]" value="1" <?php if(isset($groupPurview[$row['group_id']]))if ($groupPurview[$row['group_id']] >= 1) { ?>checked<?php } ?> /></td>
<td><input type="checkbox" name="purview[<?php echo $row['group_id']; ?>][insert]" value="1" <?php if(isset($groupPurview[$row['group_id']]))if ($groupPurview[$row['group_id']] >= 2) { ?>checked<?php } ?> /></td>
<td><input type="checkbox" name="purview[<?php echo $row['group_id']; ?>][update]" value="1" <?php if(isset($groupPurview[$row['group_id']]))if ($groupPurview[$row['group_id']] >= 4) { ?>checked<?php } ?> /></td>
<td><input type="checkbox" name="purview[<?php echo $row['group_id']; ?>][delete]" value="1" <?php if(isset($groupPurview[$row['group_id']]))if ($groupPurview[$row['group_id']] >= 8) { ?>checked<?php } ?> /></td>
</tr>
<?php } } ?>

<tr class="noborder"><td colspan="22">
<button type="submit">修改</button>
</td></tr>
</table>
</form>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
