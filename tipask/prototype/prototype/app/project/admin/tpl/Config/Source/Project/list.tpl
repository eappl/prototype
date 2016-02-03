{tpl:tpl contentHeader/}
<script	type="text/javascript">
$(document).ready(function(){
	$('#add_source_project').click(function(){
		addSourceProjectBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加媒介项目', contentType:'ajax', width:600, height:240, showOk:false, showCancel:false});
	});
});
function sourceProjectModify(mid){
	modifyProjectBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&SourceProjectId=' + mid, {title:'修改媒介项目', width:600, height:240});
}
function promptDelete(p_id, p_name){
	deleteProjectBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&SourceProjectId=' + p_id;}});
}
</script>
<fieldset><legend>操作</legend>
[	<a href="javascript:;" id="add_source_project">添加媒介项目</a>	]
</fieldset>
<fieldset><legend>媒介项目列表</legend>
<table class="table table-bordered table-striped">
<form	action="{tpl:$this.sign/}" name="form" id="form" method="post">

				</form>
<tr><th align="center" class="rowtip">媒介项目ID</th>
<th align="center" class="rowtip">媒介项目名称</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop	$SourceProjectArr $SourceProject $sourceproject_data}
<tr>
<td>{tpl:$sourceproject_data.SourceProjectId/}</td>
<td>{tpl:$sourceproject_data.name/}</td>
<td><a href="javascript:;" onclick="sourceProjectModify('{tpl:$sourceproject_data.SourceProjectId/}');">修改</a>|
<a href="{tpl:$this.sign/}&ac=detail&SourceProjectId={tpl:$sourceproject_data.SourceProjectId/}">详情配置</a>
|<a	 href="javascript:;" onclick="promptDelete('{tpl:$sourceproject_data.SourceProjectId/}','{tpl:$sourceproject_data.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset> 
</dl>
{tpl:tpl contentFooter/}
