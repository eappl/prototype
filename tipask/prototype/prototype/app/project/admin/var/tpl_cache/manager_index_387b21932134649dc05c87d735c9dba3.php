<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_manager').click(function(){
		addManagerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add', {title:'添加管理员',width:500,height:450});
	});
});
function pwdReset(gid, gname){
	pwdResetBox = divBox.showBox('<?php echo $this->sign; ?>&ac=pwdreset&id=' + gid, {title:'重置密码', width:600, height:300});
}
function managerModify(mid){
	modifyManagerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&id=' + mid, {title:'修改管理员',width:500,height:500});
}
function promptDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&id=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_manager">添加管理员</a> ]

<form id="form1" name="form1" method="post" action="">
  
  数据用户组：<select name="data_group_id" id="data_group_id" class="input-medium">
  	<option value="">请选择</option>
    <?php if (is_array($dataGroup)) { foreach ($dataGroup as $data) { ?>
    <option value="<?php echo $data['group_id']; ?>" <?php if($data['group_id']==$data_group_id) { ?>selected="selected"<?php } ?>><?php echo $data['name']; ?></option>
    <?php } } ?>
  </select>
  菜单用户组：<select name="menu_group_id" id="menu_group_id" class="input-medium">
  	<option value="">请选择</option>
    <?php if (is_array($menuGroup)) { foreach ($menuGroup as $menu) { ?>
    <option value="<?php echo $menu['group_id']; ?>" <?php if($menu['group_id']==$menu_group_id) { ?>selected="selected"<?php } ?>><?php echo $menu['name']; ?></option>
    <?php } } ?>
  </select>
  用户组：
  <select name="is_partner" id="is_partner" class="input-medium">
  	<option value="">请选择</option>
    <option value="0">内部用户</option>
    <option value="1">外部用户</option>
  </select> 
  用户名：
  <input type="text" name="username" value="<?php echo $username; ?>">
  <input type="submit" name="button" id="button" value="查询" />
  </label>
</form>
</fieldset>

<fieldset><legend>用户管理</legend>
<table class="table table-bordered table-striped" width="100%">
<tr><th align="center" class="rowtip">用户ID</th>
<th align="center" class="rowtip">用户名</th>
<th align="center" class="rowtip">菜单用户组</th>
<th align="center" class="rowtip">数据用户组</th>
<th align="center" class="rowtip">上次登陆时间</th>
<th align="center" class="rowtip">注册IP</th>
<th align="center" class="rowtip">注册时间</th>
<th align="center" class="rowtip">操作</th></tr>
<?php if (is_array($manager)) { foreach ($manager as $row) { ?>
<tr class="hover"><td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['menu_group_name']; ?></td>
<td><input type="text" value="<?php echo $row['data_group_name']; ?>" /></td>
<td><?php echo $row['last_login']; ?></td>
<td><?php echo $row['reg_ip']; ?></td>
<td><?php echo $row['reg_time']; ?></td>

<td>
<a href="javascript:;" onclick="managerModify(<?php echo $row['id']; ?>);">修改</a>
|<a  href="javascript:;" onclick="promptDelete('<?php echo $row['id']; ?>','<?php echo $row['name']; ?>')">删除</a> | <a href="javascript:;" onclick="pwdReset('<?php echo $row['id']; ?>','<?php echo $row['name']; ?>');">重置密码</a></td>
</tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>