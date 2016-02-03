{tpl:tpl contentHeader/}
<fieldset><legend> {tpl:$menu.name/} 权限</legend>

<form action="?ctl=menu/permission&ac=update.by.menu" method="post">
<input type="hidden" name="menu_id" value="{tpl:$menu_id/}" />
<table class="table table-bordered table-striped" width="100%">
<tr><th>用户组</th>
{tpl:loop $permission_list $permission_name $permission}
<th>{tpl:$permission_name/}</th>
{/tpl:loop}
</tr>
{tpl:loop $group $row}
<tr class="hover">
<td>{tpl:$row.name/}</td>
{tpl:loop $row.permission_list $p $pn}
<td><input type="checkbox" name="permission[{tpl:$row.group_id/}][{tpl:$p/}]" value="1" {tpl:if($pn == 1)}checked{/tpl:if} /></td>
{/tpl:loop}
</tr>
{/tpl:loop}

<tr class="noborder"><td colspan="22">
<button type="submit">修改</button>
</td></tr>
</table>
</form>
</fieldset>
{tpl:tpl contentFooter/}
