{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="faqtype_modify_form" id="faqtype_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>FAQ类型ID</td>
<td>{tpl:$FaqType.FaqTypeId/}</td>
</tr>

		<input type="hidden" name="FaqTypeId" id="FaqTypeId" class="span4" value="{tpl:$FaqType.FaqTypeId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$FaqType.name/}"/></td>
</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="faqtype_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#faqtype_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '失败，必须输入FAQ类型名称';
					errors[3] = '失败，必须输入FAQ类型ID';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改FAQ类型成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#faqtype_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}