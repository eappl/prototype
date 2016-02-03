{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="group_add_form" id="group_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
<table class="table table-bordered table-striped" width="100%">
	<tr>
		<th class="rowtip"><label for="name">组名</label></th><td class="rowform">
		<input type="text" name="name" id="name" class="span4" />
		<input name="ClassId" type="hidden" id="ClassId" value="1" /> </td>
	</tr>
	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="group_add_submit">提交</button></td>
	</tr>
</table>
</form>
<script type="text/javascript">
document.getElementById('name').focus();
$(function(){
	$('#group_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {
			},
			success:function(jsonResponse) {
			
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '用户组名不能为空，请确认后再次提交';
					errors[2] = '用户组名已存在，请修正后再次提交';
					errors[9] = '添加用户组失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});

				} else {
					var message = '成功添加一个用户组';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#group_add_form').ajaxForm(options);
	});

});

</script>
{tpl:tpl contentFooter/}