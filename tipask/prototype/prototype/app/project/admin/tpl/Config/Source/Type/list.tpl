{tpl:tpl contentHeader/}
<script	type="text/javascript">
$(document).ready(function(){
	$('#add_source_type').click(function(){
		addSourceTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加广告商分类',	width:600, height:250});

	});
});
function sourceModify(mid){
	modifySourceTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&SourceTypeId='	+	mid, {title:'修改广告商分类',	contentType:'ajax',	width:600, height:250});
}

function promptDelete(m_id,p_id){
	deleteSourceTypeBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&delete&SourceTypeId=' + m_id;}});
}
</script>
<fieldset><legend>操作</legend>
[	<a href="javascript:;" id="add_source_type">添加广告商分类</a>	]
</fieldset>
<fieldset><legend>广告商分类列表</legend>
<table class="table table-bordered table-striped">
<form	action="{tpl:$this.sign/}" name="form" id="form" method="post">

				</form>
<tr><th align="center" class="rowtip">广告商分类ID</th>
<th align="center" class="rowtip">广告商分类名称</th>
<th>操作</th></tr>
{tpl:loop	$SourceTypeArr $SourceType $sourcetype_data}
<tr>
<td>{tpl:$sourcetype_data.SourceTypeId/}</td>
<td>{tpl:$sourcetype_data.name/}</td>
<td><a href="javascript:;" onclick="sourceModify('{tpl:$sourcetype_data.SourceTypeId/}');">修改</a>
|<a	 href="javascript:;" onclick="promptDelete('{tpl:$sourcetype_data.SourceTypeId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}
