{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_research').click(function(){
		addResearchBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加调研',width:600,height:400});
	});
});
function researchModify(mid){
	modifyResearchBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&ResearchId=' + mid, {title:'修改调研',width:600, height:400});
}

function promptDelete(ResearchName,m_id){
	deleteResearchBox = divBox.confirmBox({content:'是否删除 '+ResearchName+'?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&ResearchId=' + m_id;}});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_research">添加调研</a> ]
</fieldset>
<fieldset><legend>调研列表</legend>
<table class="table table-bordered table-striped">
<tr>
<th align="center" class="rowtip">调研ID</th>
<th align="center" class="rowtip">调研标题</th><th>调研内容</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $ResearchArr $Research $research_data}
<tr>
<td>{tpl:$research_data.ResearchId/}</td>
<td>{tpl:$research_data.ResearchName/}</td>
<td>{tpl:$research_data.ResearchContent/}</td>
<td><a href="javascript:;" onclick="researchModify('{tpl:$research_data.ResearchId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$research_data.ResearchName/}','{tpl:$research_data.ResearchId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
{tpl:tpl contentFooter/}