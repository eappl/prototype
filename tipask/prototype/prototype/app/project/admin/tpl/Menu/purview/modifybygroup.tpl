{tpl:tpl contentHeader/}
<fieldset><legend> {tpl:$group.name/} 组权限</legend>


<form action="?ctl=menu/permission&ac=update.by.group" method="post">
<input type="hidden" name="group_id" value="{tpl:$group_id/}" />
<table class="table table-bordered table-striped" width="100%">
<tr><th>菜单</th>
<th>权限</th></tr>
{tpl:loop $menu $row}
<tr class="hover">
<td><label class="checkbox">{tpl:$row.prefix/}{tpl:$row.name/}</label></td>
<td>
{tpl:loop $row.permission_detail $p $p_info}
<p>
<input type="checkbox"  name="permission[{tpl:$row.menu_id/}][{tpl:$p/}]" value="1" {tpl:if($p_info.selected == 1)}checked{/tpl:if} /> {tpl:$p_info.permission_name/} 
{/tpl:loop}
</td>
</tr>
{/tpl:loop}

<tr class="noborder"><td colspan="22">
<button type="submit" class="btn btn-info btn-small">修改</button>
</td></tr>
</table>
</form>
</fieldset>
<script type="text/javascript">

</script>
{tpl:tpl contentFooter/}