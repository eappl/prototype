{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_source_action').click(function(){
		addSourceActionBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加活动', contentType:'ajax', width:600, height:240, showOk:false, showCancel:false});
	});
});
function actionModify(mid){
	modifyActionBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&SourceActionId=' + mid, {title:'修改活动', width:600, height:240});
}

function promptDelete(p_id, p_name){
	deleteSourceBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&SourceActionId=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_source_action">添加活动</a> ]
</fieldset>
<fieldset><legend>活动列表</legend>
<table class="table table-bordered table-striped">

<tr><th align="center" class="rowtip">活动ID</th>
<th align="center" class="rowtip">活动名称</th>
<th align="center" class="rowtip">操作</th>
</tr>
{tpl:loop $SourceActionArr $SourceAction $source_action_data}
<tr>
<td>{tpl:$source_action_data.SourceActionId/}</td>
<td>{tpl:$source_action_data.name/}</td>
<td><a href="javascript:;" onclick="actionModify('{tpl:$source_action_data.SourceActionId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$source_action_data.SourceActionId/}','{tpl:$source_action_data.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}
