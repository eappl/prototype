{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_source').click(function(){
		addSourceBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加广告商', width:600, height:250});
	});
});
function sourceModify(mid){
	modifySourceBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&SourceId=' + mid, {title:'修改广告商', width:600, height:250});

}

function promptDelete(p_id, p_name){
	deleteSourceBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&SourceId=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_source">添加广告商</a> ]
</fieldset>
<fieldset><legend>广告商列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择广告商分类
			<select name = "SourceTypeId" id = "SourceTypeId">
			<option value = 0 {tpl:if (0==$SourceTypeId)}selected{/tpl:if}>全部</option>
			{tpl:loop $SourceTypeList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$SourceTypeId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>

<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<tr><th align="center" class="rowtip">广告商ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">所属广告商分类</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $SourceArr $Source $source_data}
<tr>
<td>{tpl:$source_data.SourceId/}</td>
<td>{tpl:$source_data.name/}</td>
<td>{tpl:$source_data.SourceTypeName/}</td>
<td><a href="javascript:;" onclick="sourceModify('{tpl:$source_data.SourceId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$source_data.SourceId/}','{tpl:$source_data.SourceTypeName/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}