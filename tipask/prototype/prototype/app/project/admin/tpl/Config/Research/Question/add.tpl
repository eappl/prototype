{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="question_add_form" name="question_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
		<tr class="hover">
<td>问题内容</td>
<td><input type="text" name="QuestionContent" id="QuestionContent" class="span4"   size="50" /> </td>
</tr>
		<tr class="hover">
			<td>选择调研</td>
			<td align="left">
			
			<select name = "ResearchId" id = "ResearchId">
			{tpl:loop $ResearchList $key $research_data}
			<option value = {tpl:$key/}>{tpl:$research_data.ResearchName/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>
		<tr class="hover">
			<td>选择答案类型</td>
			<td align="left">
			
			<select name = "AnswerType" id = "AnswerType">
			{tpl:loop $AnswerTypeList $key $answertype_data}
			<option value = {tpl:$key/} >{tpl:$answertype_data/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>
<tr>
	<td>回答</td>
	<td><textarea rows="5" cols="40" name="Answer" id="Answer"></textarea></td>
</tr>
<tr class="noborder"><td></td>
		<td><button type="submit" id="question_add_submit">提交</button></td>
</tr>
	</table>
	</form>
	 

<script type="text/javascript">
$(function(){
	$('#question_add_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '失败，必须选定一个调研';
					errors[3] = '失败，必须输入问题内容';
					errors[4] = '失败，必须选定回答类型';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加调研问题成功';
					var ResearchId = $("#ResearchId").val();
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}&ResearchId='+ResearchId);}});
				}
			}
		};
		$('#question_add_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}