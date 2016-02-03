{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="faq_modify_form" id="faq_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>FAQ ID</td>
<td>{tpl:$Faq.FaqId/}</td>
</tr>

		<input type="hidden" name="FaqId" id="FaqId" class="span4" value="{tpl:$Faq.FaqId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$Faq.name/}"/></td>
</tr>

		<tr class="hover">
			<td>选择FAQ分类</td>
			<td align="left">
			<select name = "FaqTypeId" id = "FaqTypeId">
			{tpl:loop $FaqTypeList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$Faq.FaqTypeId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>
<tr>
<td>回答</td>
<td>
<?php echo $editor->editor("Answer",$Faq['Answer']); ?>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="faq_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#faq_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个FAQ分类';
					errors[2] = '失败，必须输入FAQ名称';
					errors[3] = '失败，必须输入FAQID';
					errors[3] = '失败，必须输入FAQ答案';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改FAQ成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&FaqTypeId=' + jsonResponse.FaqTypeId);}});

				}
			}
		};
		$('#faq_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}