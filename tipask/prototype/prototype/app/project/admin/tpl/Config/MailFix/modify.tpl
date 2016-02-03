{tpl:tpl contentHeader/}
<div class="br_bottom"></div>

<form id="fix_update_form" name="fix_update_form" action="{tpl:$this.sign/}&ac=update" metdod="post">
<input type="hidden" name="FixId" value="{tpl:$MailFixInfo.FixId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>邮箱后缀ID</td>
<td align="left">{tpl:$MailFixInfo.FixId/}</td>
</tr>
<tr class="hover">
<td>邮箱后缀</td>
<td align="left"><input name="SubFix" id="SubFix" type="text" class="span4" id="name" value="{tpl:$MailFixInfo.SubFix/}" size="50" /></td>
</tr>


<tr class="noborder"><td></td>
<td><button type="submit" id="fix_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
document.getElementById('SubFix').focus();
$('#fix_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
			
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '后缀ID不能为空，请修正后再次提交';
				errors[2] = '邮箱后缀不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改邮箱后缀成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#fix_update_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}