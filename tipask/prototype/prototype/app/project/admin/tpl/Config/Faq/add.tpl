{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="faq_add_form" name="faq_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

	
		<tr class="hover">
			<td>FAQ名称</td>
			<td align="left"><input name="name" id="name" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>
		
		<tr class="hover">
			<td>选择FAQ分类</td>
			<td align="left">
			<select name = "FaqTypeId" id = "FaqTypeId">
			{tpl:loop $FaqTypeList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$FaqTypeId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>
<tr>
<td>回答</td>
<td>
<?php echo $editor->editor("Answer",""); ?>
</td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="faq_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('name').focus();
$('#faq_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '失败，必须选定一个FAQ分类';
				errors[2] = '失败，必须输入FAQ名称';
				errors[3] = '失败，必须输入FAQ答案';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&FaqTypeId=' + jsonResponse.FaqTypeId);}});
			}
		}
	};
	$('#faq_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}