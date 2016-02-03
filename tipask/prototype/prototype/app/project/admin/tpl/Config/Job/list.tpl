{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_job').click(function(){
		addJobBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加职业',width:500,height:300});
	});
});
function jobModify(m_id,p_id){
	modifyJobBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&JobId=' + m_id + '&AppId=' + p_id, {title:'修改职业', width:500, height:300});
}

function promptDelete(m_id,p_id){
	deleteProductBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&JobId=' + m_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_job">添加职业</a> ]
</fieldset>
<fieldset><legend>职业列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择游戏
			<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">
<tr><th align="center" class="rowtip">职业ID</th>
<th align="center" class="rowtip">职业名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $JobArr $App $app_data}
	{tpl:loop $app_data $Job $job_data}
<tr>
<td>{tpl:$job_data.JobId/}</td>
<td>{tpl:$job_data.name/}</td>
<td>{tpl:$job_data.AppName/}</td>
<td><a href="javascript:;" onclick="jobModify('{tpl:$job_data.JobId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$job_data.JobId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}
