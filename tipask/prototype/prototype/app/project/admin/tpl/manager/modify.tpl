{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="user_update_form" id="user_update_form" action="?ctl=manager&ac=update" method="post">
<table class="table table-bordered table-striped" width="100%">
<input type="hidden" id="id" name="id" value="{tpl:$admin.id/}">
	<tr><th class="rowtip">用户名</th><td class="rowform">
	{tpl:$admin.name/} </td></tr>

	<tr>
		<th><label for="oldpasswd">原密码</label></th><td>
		<input type="password" name="oldpasswd" id="oldpasswd" class="span4" /></td>
	</tr>
	<tr>
		<th><label for="newpasswd">新密码</label></th><td>
		<input type="password" name="newpasswd" id="newpasswd" class="span4" /></td>
	</tr>
	<tr>
		<th><label for="confirm">密码确认</label></th><td>
		<input type="password" name="confirm" id="confirm" class="span4" /></td>
	</tr>
<tr>
  <th>用户分类</th>
  <td><label><input type="radio" name="is_partner" id="radio" value="0" {tpl:if($admin.is_partner==0)}checked="checked"{/tpl:if} /> 内部用户</label>
      <label><input type="radio" name="is_partner" id="radio2" value="1" {tpl:if($admin.is_partner==1)}checked="checked"{/tpl:if}/> 外部用户</label></td>
</tr>
<tr>
	<th><label for="group">菜单用户组</label></th><td>

	<select name="menu_group_id" id="menu_group_id">
	{tpl:loop $menuGroup $row}<option value="{tpl:$row.group_id/}"{tpl:if($admin.menu_group_id==$row.group_id)}selected{/tpl:if}>{tpl:$row.name/}</option>{/tpl:loop}
	</select>
	
	</td>
</tr>
<tr>
	<th><label for="group">数据用户组</label></th><td>

	{tpl:loop $dataGroup $row}<label><input type="checkbox" name="data_groups[]" id="data_groups[]" value="{tpl:$row.group_id/}" {tpl:loop $admin.data_groups $groups}{tpl:if($groups == $row.group_id)}checked="checked"{/tpl:if}{/tpl:loop} />{tpl:$row.name/}&nbsp;</label>{/tpl:loop}
	</td>
</tr>
    
	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="user_update_form">提交</button></td>
	</tr>
</table>
</form>
</fieldset>

<script type="text/javascript">
//document.getElementById('name').focus();
$(function(){
	$('#user_update_form').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) 
			{

			},
			success:function(jsonResponse) 
			{
				if (jsonResponse.errno) 
				{
					divBox.alertBox(jsonResponse.message,function(){});
				} 
				else 
				{
					divBox.confirmBox({content:jsonResponse.message,ok:function(){windowParent.getRightHtml(jsonResponse.goto);}});
				}
			}
		};
		$('#user_update_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
