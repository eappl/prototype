{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_group').click(function(){
		addGroupBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加菜单权限组', width:600, height:200});
	});
});
function groupModify(mid){
	modifyGroupBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&group_id=' + mid, {title:'修改数据权限组', contentType:'ajax', width:600, height:200, showOk:false, showCancel:false});
}
function promptDelete(p_id, p_name){
	deleteGroupBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&group_id=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_group">添加菜单权限组</a> ]
</fieldset>

<fieldset><legend>列表</legend>
<table class="table table-bordered table-striped" width="100%">
<tr><th align="center" class="rowtip">权限组ID</th>
<th align="center" class="rowtip">权限组名称</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $groupArr $row}
<tr class="hover"><td>{tpl:$row.group_id/}</td>
<td><a href="?ctl=manager&menu_group_id={tpl:$row.group_id/}">{tpl:$row.name/}</a></td>
<td>
<a href="javascript:;" onclick="groupModify('{tpl:$row.group_id/}')">修改</a>
| <a  href="javascript:;" onclick="promptDelete('{tpl:$row.group_id/}','{tpl:$row.name/}')">删除</a>
| <a href="?ctl=menu/permission&ac=modify.by.group&group_id={tpl:$row.group_id/}">菜单权限</a></td>
</tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}