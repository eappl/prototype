{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_producttype').click(function(){
		addAppBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'产品类型',width:500,height:350});
	});
});
function producttypeModify(m_id,p_id){
	modifyProducttypeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&&ProductTypeId=' + m_id + '&AppId=' + p_id, {title:'修改产品类型', width:500, height:350});
}

function promptDelete(m_id,p_id){
	deleteProducttypeBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&ProductTypeId=' + m_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_producttype">添加产品类型</a> ]
</fieldset>
<fieldset><legend>产品类型列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择游戏
			<select name = "AppId" id = "AppId">
			<option value=0 {tpl:if (0==$AppId)}selected{/tpl:if}>全部</option>
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
<input type="submit" name="submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">

<tr>
<th align="center" class="rowtip">产品类型ID</th>
<th align="center" class="rowtip">产品类型名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th>
</tr>
{tpl:loop $ProductTypeArr $App $app_data}
	{tpl:loop $app_data $ProductType $producttype_data}
<tr>
<td>{tpl:$producttype_data.ProductTypeId/}</td>
<td>{tpl:$producttype_data.name/}</td>
<td>{tpl:$producttype_data.AppName/}</td>
<td><a href="javascript:;" onclick="producttypeModify('{tpl:$producttype_data.ProductTypeId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$producttype_data.ProductTypeId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}