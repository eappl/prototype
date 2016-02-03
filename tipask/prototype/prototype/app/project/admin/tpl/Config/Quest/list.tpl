{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_quest').click(function(){
		addQuestBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加任务', width:600, height:300});
	});
});
function questModify(m_id,p_id){
	modifyQuestBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&QuestId=' + m_id + '&AppId=' + p_id, {title:'修改任务', width:600, height:300});
}

function promptDelete(m_id,p_id){
	deleteQuestBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&InstMapId=' + m_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_quest">添加任务</a> ]
</fieldset>
<fieldset><legend>任务列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
选择游戏	<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">
<tr>
<th align="center" class="rowtip">任务ID</th>
<th align="center" class="rowtip">任务名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $QuestArr $App $app_data}
	{tpl:loop $app_data $Quest $quest_data}
<tr>
<td>{tpl:$quest_data.QuestId/}</td>
<td>{tpl:$quest_data.name/}</td>
<td>{tpl:$quest_data.AppName/}</td>
<td><a href="javascript:;" onclick="questModify('{tpl:$quest_data.QuestId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$quest_data.QuestId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}