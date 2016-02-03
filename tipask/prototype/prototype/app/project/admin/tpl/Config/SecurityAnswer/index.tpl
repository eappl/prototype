{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_question').click(function(){
		addQuestionBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加密保问题',width:500,height:200});
	});
});
function promptDelete(p_id, p_name){
	deleteQuestionBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&QuestionId=' + p_id;}});
}
function questionModify(mid){
	modifyQuestionBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&QuestionId=' + mid, {title:'修改密保问题',width:500,height:200});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_question">添加密保问题</a> ]
</fieldset>

<fieldset><legend>密保问题列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">ID</th>
    <th align="center" class="rowtip">问题</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $QuestionArr $Question}
  <tr class="hover">
    <td>{tpl:$Question.QuestionId/}</td>
    <td>{tpl:$Question.Question/}</td>
    <td><a  href="javascript:;" onclick="promptDelete('{tpl:$Question.QuestionId/}','{tpl:$Question.Question/}')">删除</a> |<a href="javascript:;" onclick="questionModify({tpl:$Question.QuestionId/});">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}