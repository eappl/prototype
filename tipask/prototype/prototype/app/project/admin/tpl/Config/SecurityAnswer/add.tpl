{tpl:tpl contentHeader/}
<div class="br_bottom"></div>

<form id="question_add_form" name="question_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>问题内容</td>
<td align="left"><input name="Question" id="Question" type="text" class="span4" id="name" value="" size="50" /></td>
</tr>

<tr class="noborder"><td></td>
<td><button type="submit" id="question_add_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
document.getElementById('Question').focus();
$('#question_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[2] = '问题内容不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加密保问题成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#question_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}