{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_question').click(function(){
		addQuestionBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加问题', width:600, height:480});
	});
});
function questionModify(mid){
	modifyQuestionBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&QuestionId=' + mid, {title:'修改问题', width:600, height:500});
}

function promptDelete(QuestionContent,m_id,p_id){
	deleteQuestionBox = divBox.confirmBox({content:'是否删除 ' + QuestionContent + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&QuestionId=' + m_id + '&ResearchId='+ p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_question">添加问题</a> ]
</fieldset>
<fieldset><legend>问题列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择调研
			<select name = "ResearchId" id = "ResearchId">
			<option value = 0 {tpl:if (0==$ResearchId)}selected{/tpl:if}>全部</option>
			{tpl:loop $ResearchList $key $research}
			<option value = {tpl:$key/} {tpl:if ($key==$ResearchId)}selected{/tpl:if}>{tpl:$research.ResearchName/}</option>
			{/tpl:loop}
			</select>

<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<tr>
<th align="center" class="rowtip">问题ID</th>
<th align="center" class="rowtip">问题</th>
<th align="center" class="rowtip">所属调研</th>
<th align="center" class="rowtip">回答种类</th>
<th align="center" class="rowtip">回答</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $QuestionArr $Question $question_data}
<tr>
<td>{tpl:$question_data.QuestionId/}</td>
<td>{tpl:$question_data.QuestionContent/}</td>
<td>{tpl:$question_data.ResearchName/}</td>
<td>{tpl:$question_data.AnswerTypeName/}</td>
<td>{tpl:$question_data.Answer/}</td>
<td><a href="javascript:;" onclick="questionModify('{tpl:$question_data.QuestionId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$question_data.QuestionContent/}','{tpl:$question_data.QuestionId/}','{tpl:$question_data.ResearchId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}