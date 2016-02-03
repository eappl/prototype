{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="question_modify_form" id="question_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>问题ID</td>
<td>{tpl:$Question.QuestionId/} </td>
</tr>

		<input type="hidden" name="QuestionId" id="QuestionId" class="span4" value="{tpl:$Question.QuestionId/}"/>

<td>问题内容</td>
<td><input type="text" name="QuestionContent" id="QuestionContent" class="span4"   size="50" value="{tpl:$Question.QuestionContent/}"/>  </td>
</tr>

		<tr class="hover">
			<td>选择调研</td>
			<td align="left">
			
			<select name = "ResearchId" id = "ResearchId">
			{tpl:loop $ResearchList $key $research_data}
			<option value = {tpl:$key/} {tpl:if ($key==$Question.ResearchId)}selected{/tpl:if}>{tpl:$research_data.ResearchName/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>
		<tr class="hover">
			<td>选择答案类型</td>
			<td align="left">
			
			<select name = "AnswerType" id = "AnswerType">
			{tpl:loop $AnswerTypeList $key $answertype_data}
			<option value = {tpl:$key/} {tpl:if ($key==$Question.AnswerType)}selected{/tpl:if}>{tpl:$answertype_data/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>
<tr>
<td>回答</td>
<td>
<textarea rows="5" cols="40" name="Answer" id="Answer">{tpl:$Question.Answer/}</textarea></td>
<td></td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="question_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
<script type="text/javascript">
$(function(){
	$('#question_modify_submit').click(function(){
		
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
					errors[5] = '失败，必须输入问题编号';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改调研问题成功';
					var ResearchId = $("#ResearchId").val();
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}&ResearchId='+ResearchId);}});
				}
			}
		};
		$('#question_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}