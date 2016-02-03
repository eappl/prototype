{tpl:tpl contentHeader/}
<script type="text/javascript">
function getsourcebytype()
{
	source_type_id=$("#SourceTypeId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/source&ac=get.source.by.type&SourceTypeId="+source_type_id.val(),
		
		success: function(msg)
		{
			$("#SourceId").html(msg);
		}
	});
	//*/
}
$(document).ready(function(){
	$('#add_sourcedetail').click(function(){
		addsSourceDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加广告位', width:600, height:550});
	});
});
function sourceDetailModify(mid){
	modifySourceDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&SourceDetail=' + mid, {title:'修改广告位', width:600, height:300});
}

function promptDelete(p_id, p_name){
	deleteSourceDetailBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&SourceDetail=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_sourcedetail">添加广告位</a> ]
</fieldset>
<fieldset><legend>广告位列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
选择广告商分类
			<select name = "SourceTypeId" id = "SourceTypeId" onchange="getsourcebytype()">
	<option value = 0 {tpl:if (0==$SourceTypeId)}selected{/tpl:if}> 全部 </option>
				{tpl:loop $SourceTypeList $key $sourcetype}
			<option value = {tpl:$key/} {tpl:if ($key==$SourceTypeId)}selected{/tpl:if}>{tpl:$sourcetype.name/}</option>
			{/tpl:loop}
			</select>

	选择广告商
			<select name = "SourceId" id = "SourceId" >
				<option value = 0 {tpl:if (0==$SourceId)}selected{/tpl:if}> 全部 </option>
				{tpl:loop $SourceList $key $source}
					
			<option value = {tpl:$key/} {tpl:if ($key==$SourceId)}selected{/tpl:if}>{tpl:$source.name/}</option>
			{/tpl:loop}
			</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">
<tr><th align="center" class="rowtip">广告位ID</th>
<th align="center" class="rowtip">所属广告商分类</th>
<th align="center" class="rowtip">所属广告商</th>
<th align="center" class="rowtip">广告位名称</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $SourceDetailArr $SourceType $SourceType_data}
	{tpl:loop $SourceType_data $Source $Source_data}
		{tpl:loop $Source_data $SourceDetail $SourceDetail_data}
<tr>
<td>{tpl:$SourceDetail/}</td>
<td>{tpl:$SourceDetail_data.SourceTypeName/}</td>
<td>{tpl:$SourceDetail_data.SourceName/}</td>
<td>{tpl:$SourceDetail_data.name/}</td>
<td><a href="javascript:;" onclick="sourceDetailModify('{tpl:$SourceDetail/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$SourceDetail_data.SourceDetail/}','{tpl:$SourceDetail_data.name/}')">删除</a>
</td>
</tr>
		{/tpl:loop}
	{/tpl:loop}
{/tpl:loop}

</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}