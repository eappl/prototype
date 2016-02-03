{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="user_add_form" id="user_add_form" action="?ctl=manager&ac=insert" method="post">
<table class="table table-bordered table-striped" width="100%">
<tr><th class="rowtip"><label for="name">用户名</label></th><td class="rowform">
<input type="text" name="name" id="name" class="span4" /></td></tr>
<tr><th><label for="passwd">密码</label></th><td>
<input type="text" name="password" id="password" class="span4" value="{tpl:$pass/}" /></td></tr>
<tr>
<th><label for="confirm">密码确认</label></th><td>
<input type="text" name="confirm" id="confirm" class="span4" value="{tpl:$pass/}" /></td></tr>
<tr>
  <th>用户分类</th>
  <td><label><input type="radio" name="is_partner" id="radio" value="0" checked="checked" /> 内部用户</label>
      <label><input type="radio" name="is_partner" id="radio2" value="1" /> 外部用户</label></td>
</tr>
<tr>
	<th><label for="group">菜单用户组</label></th><td>

	<select name="menu_group_id" id="menu_group_id">
	{tpl:loop $menuGroup $row}<option value="{tpl:$row.group_id/}">{tpl:$row.name/}</option>{/tpl:loop}
	</select>	
	</td>
</tr>
<tr>
	<th><label for="group">数据用户组</label></th><td>

	{tpl:loop $dataGroup $row}<input type="checkbox" name="data_groups[]" id="data_groups[]" value="{tpl:$row.group_id/}" />{tpl:$row.name/}&nbsp;{/tpl:loop}</td>
</tr>
<tr class="noborder"><th></th><td>
<button type="submit" id="user_add_submit">提交</button></td></tr>
</table>
</form>

<script type="text/javascript">
document.getElementById('name').focus();
$(function(){
	$('#user_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
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
		$('#user_add_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
