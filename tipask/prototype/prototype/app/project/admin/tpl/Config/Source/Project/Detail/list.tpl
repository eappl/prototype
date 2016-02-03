{tpl:tpl contentHeader/}
<script	type="text/javascript">
$(document).ready(function(){
	$('#add_source_project_detail').click(function(){
		addSourceProjectDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=add.detail&SourceProjectId='+{tpl:$SourceProjectId/}, {title:'添加媒介项目详情',width:600, height:480});
	});
});
function sourceProjectDetailModify(p_id,did){
	modifyProjectDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=modify.detail&SourceProjectId=' + p_id + '&SourceProjectDetailId=' + did, {title:'项目详情', width:600, height:500});
}

function promptDelete(p_id,did){
	deleteProjectDetailBox = divBox.confirmBox({content:'是否删除 ' + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete.detail&SourceProjectId=' + p_id + '&SourceProjectDetailId=' + did;}});
								
}
</script>
<fieldset><legend>操作</legend>
[	<a href="javascript:;" id="add_source_project_detail">添加媒介项目详情</a>	] &nbsp;&nbsp;&nbsp;&nbsp;{tpl:$export_var/}
</fieldset>
<fieldset><legend>项目：{tpl:$SourceProject.name/}</legend>
<table class="table table-bordered table-striped">
<tr><th width="30">广告商</th><th width="30">广告位</th><th width="30">开始日期</th><th width="30">结束日期</th><th width="30">成本</th><th width="150">连接参数</th><th width="65">操作</th></tr>
{tpl:loop	$SourceProjectDetail $key $sourceproject_detail}
<tr>
<td>{tpl:$sourceproject_detail.SourceName/}</td>
<td>{tpl:$sourceproject_detail.SourceDetailName/}</td>
<td>{tpl:$sourceproject_detail.StartDate/}</td>
<td>{tpl:$sourceproject_detail.EndDate/}</td>
<td>{tpl:$sourceproject_detail.Cost/}</td>
<td>{tpl:$sourceproject_detail.SourceUrl/}</td>
<td><a href="javascript:;" onclick="sourceProjectDetailModify('{tpl:$SourceProjectId/}','{tpl:$sourceproject_detail.SourceProjectDetailId/}');">修改</a>
|<a	 href="javascript:;" onclick="promptDelete('{tpl:$SourceProjectId/}','{tpl:$sourceproject_detail.SourceProjectDetailId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}