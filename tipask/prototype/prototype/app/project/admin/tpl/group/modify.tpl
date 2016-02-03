
<fieldset><legend>操作</legend>
[ <a href="?ctl=group">列表</a> ]
</fieldset>
<fieldset>
<legend>用户组管理</legend>
<form name="group_update_form" id="group_update_form" action="?ctl=group&ac=update" method="post">
<table class="tbv" width="100%">
<INPUT TYPE="hidden" NAME="group_id" id="group_id" value="{tpl:$group.group_id/}">
	<tr>
		<th class="rowtip"><label for="name">组ID</label></th><td class="rowform">
		{tpl:$group.group_id/}</td><td>&nbsp;</td>
	</tr>
	<tr>
		<th class="rowtip"><label for="name">组名</label></th><td class="rowform">
		<input type="text" name="name" id="name" class="span4" value="{tpl:$group.name/}"/> * </td><td>&nbsp;</td>
	</tr>

	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="group_update_submit">提交</button></td><td>&nbsp;</td>
	</tr>
</table>
</form>
</fieldset>
<script type="text/javascript">
document.getElementById('name').focus();
$(function(){
	$('#group_update_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '用户组名不能为空，请确认后再次提交';
					errors[9] = '添加用户组失败，请修正后再次提交';
					divBox.showBox(errors[jsonResponse.errno]);
				} else {
					var message = '成功修改一个用户组';
					divBox.showBox(message, {onok:function(){document.location='?ctl=group';},showCancel:false});
				}
			}
		};
		$('#group_update_form').ajaxForm(options);
	});
});

</script>
