{tpl:tpl contentHeader/}
<fieldset><legend>操作</legend>
[ <a href="?ctl=group&ac=add">添加组</a> ]
</fieldset>

<fieldset><legend>列表</legend>
<form name="group_list_form" id="group_list_form" action="?ctl=group" method="post">
<table class="table table-bordered table-striped" width="100%">
<tr><th width="60">ID</th><th>名称</th><th>操作</th></tr>
{tpl:loop $group $row}
<tr class="hover"><td>{tpl:$row.group_id/}</td>
<td>{tpl:$row.name/}</a></td>
<td>
<a href="?ctl=group&ac=modify&group_id={tpl:$row.group_id/}">修改</a>
| <a href="?ctl=group&ac=delete&group_id={tpl:$row.group_id/}" onclick="return confirm('确定要删除管理员组？');">删除</a>
| <a href="?ctl=menu/permission&ac=modify.by.group&group_id={tpl:$row.group_id/}">菜单权限</a>
| <a href="?ctl=config/permission&ac=list.partner.permission&group_id={tpl:$row.group_id/}">数据权限</a></td>
</tr>
{/tpl:loop}
</table>
</form>
</fieldset>
{tpl:tpl contentFooter/}
