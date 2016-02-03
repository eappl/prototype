{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_manager').click(function(){
		addManagerBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加管理员',width:500,height:450});
	});
});
function pwdReset(gid, gname){
	pwdResetBox = divBox.showBox('{tpl:$this.sign/}&ac=pwdreset&id=' + gid, {title:'重置密码', width:600, height:300});
}
function managerModify(mid){
	modifyManagerBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&id=' + mid, {title:'修改管理员',width:500,height:500});
}
function promptDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&id=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_manager">添加管理员</a> ]

<form id="form1" name="form1" method="post" action="">
  
  数据用户组：<select name="data_group_id" id="data_group_id" class="input-medium">
  	<option value="">请选择</option>
    {tpl:loop $dataGroup $data}
    <option value="{tpl:$data.group_id/}" {tpl:if ($data.group_id==$data_group_id) }selected="selected"{/tpl:if}>{tpl:$data.name/}</option>
    {/tpl:loop}
  </select>
  菜单用户组：<select name="menu_group_id" id="menu_group_id" class="input-medium">
  	<option value="">请选择</option>
    {tpl:loop $menuGroup $menu}
    <option value="{tpl:$menu.group_id/}" {tpl:if ($menu.group_id==$menu_group_id) }selected="selected"{/tpl:if}>{tpl:$menu.name/}</option>
    {/tpl:loop}
  </select>
  用户组：
  <select name="is_partner" id="is_partner" class="input-medium">
  	<option value="">请选择</option>
    <option value="0">内部用户</option>
    <option value="1">外部用户</option>
  </select> 
  用户名：
  <input type="text" name="username" value="{tpl:$username/}">
  <input type="submit" name="button" id="button" value="查询" />
  </label>
</form>
</fieldset>

<fieldset><legend>用户管理</legend>
<table class="table table-bordered table-striped" width="100%">
<tr>
<th align="center" class="rowtip">用户ID</th>
<th align="center"" class="rowtip">用户名</th>
<th align="center" class="rowtip">菜单用户组</th>
<th align="center" class="rowtip">数据用户组</th>
<th align="center" class="rowtip">上次登陆时间</th>
<th align="center" class="rowtip">注册IP</th>
<th align="center" class="rowtip">注册时间</th>
<th align="center" class="rowtip">操作</th>
</tr>
{tpl:loop $manager $row}
<tr class="hover"><td>{tpl:$row.id/}</td>
<td >{tpl:$row.name/}</td>
<td>{tpl:$row.menu_group_name/}</td>
<td><input type="text" value="{tpl:$row.data_group_name/}" /></td>
<td>{tpl:$row.last_login/}</td>
<td>{tpl:$row.reg_ip/}</td>
<td>{tpl:$row.reg_time/}</td>

<td>
<a href="javascript:;" onclick="managerModify({tpl:$row.id/});">修改</a>|

<?php if($this->manager->name != $row['name']): ?>
<a  href="javascript:;" onclick="promptDelete('{tpl:$row.id/}','{tpl:$row.name/}')">删除</a>|
<?php endif; ?>

<a href="javascript:;" onclick="pwdReset('{tpl:$row.id/}','{tpl:$row.name/}');">重置密码</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}