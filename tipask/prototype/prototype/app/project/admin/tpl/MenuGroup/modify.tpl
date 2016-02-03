{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="group_update_form" id="group_update_form" action="{tpl:$this.sign/}&ac=update" method="post">
<table class="table table-bordered table-striped" width="100%">
<INPUT TYPE="hidden" NAME="group_id" id="group_id" value="{tpl:$group.group_id/}">
	<tr>
		<th class="rowtip"><label for="name">组ID</label></th><td class="rowform">
		{tpl:$group.group_id/}</td>
	</tr>
	<tr>
		<th class="rowtip"><label for="name">组名</label></th><td class="rowform">
		<input type="text" name="name" id="name" class="span4" value="{tpl:$group.name/}"/>
		<input name="ClassId" type="hidden" id="ClassId" value="1" /></td>
	</tr>

	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="group_update_submit">提交</button></td>
	</tr>
</table>
</form>
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
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '成功修改一个用户组';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#group_update_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}
