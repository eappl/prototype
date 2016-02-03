{tpl:tpl contentHeader/}
<p>密码已被管理员重置，必须重新设置。</p>
<form name="manager_update_form" id="manager_update_form" action="?ctl=modify.pwd&ac=pwdcompel.update" method="post">
<table class="tbv" width="100%">
<input type="hidden" id="id" name="id" value="{tpl:$admin.id/}">
	<tr><th class="rowtip">用户名</th><td class="rowform">
	{tpl:$admin.name/} </td></tr>

	<tr>
		<th><label for="newpasswd">新密码</label></th><td>
		<input type="password" name="newpasswd" id="newpasswd" class="span4" /></td>
	</tr>
	<tr>
		<th><label for="confirm">密码确认</label></th><td>
		<input type="password" name="confirm" id="confirm" class="span4" /></td>
	</tr>
	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="manager_update_form">提交</button></td>
	</tr>
</table>
</form>
<script type="text/javascript">
//document.getElementById('name').focus();
$(function(){
	$('#manager_update_form').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = "原密码不正确，请确认后再次提交";
					errors[2] = '密码两次输入不一致，请确认后再次提交';
					errors[3] = '密码不能少于6位或大于18位，请修正后再次提交';
					errors[4] = '权限错误！';
					errors[5] = '密码强度不够！<br>* 至少8个字符<br>';
					errors[9] = '修改用户失败，请修正后再次提交';
					divBox.showBox(errors[jsonResponse.errno]);
				} else {
					var message = '用户修改成功!';
					divBox.showBox(message, {onok:function(){document.location = '/';},showCancel:false});
				}
			}
		};
		$('#manager_update_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
