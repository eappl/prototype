{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="manager_update_form" id="manager_update_form" action="?ctl=manager&ac=pwdreset.update" method="post">
<table class="table table-bordered table-striped" width="100%">
<input type="hidden" id="id" name="id" value="{tpl:$admin.id/}">
	<tr><th class="rowtip">用户名</th><td class="rowform">
	{tpl:$admin.name/} </td></tr>

	<tr>
		<th><label for="newpasswd">新密码</label></th><td>
		<input type="password" name="newpasswd" id="newpasswd" class="span4" />  </td>
	</tr>
	<tr>
		<th><label for="confirm">密码确认</label></th><td>
		<input type="password" name="confirm" id="confirm" class="span4" />  </td>
	</tr>
	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="manager_update_form" class="btn btn-info btn-small">提交</button></td>
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
					errors[2] = '密码两次输入不一致，请确认后再次提交';
					errors[3] = '密码不能少于6位或大于18位，请修正后再次提交';
					errors[4] = '权限错误！';
					errors[9] = '修改用户失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '密码重置成功!';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#manager_update_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}