{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_product').click(function(){
		addProductBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加产品', width:600, height:450});
	});
});
function productModify(m_id,p_id){
	modifyProductBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&ProductId=' + m_id + '&AppId=' + p_id, {title:'修改产品', width:600, height:450});
}

function promptDelete(m_id,p_id){
	deleteProductBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&ProductId=' + m_id;}});
}
function getproducttype()
{
	app=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/product/type&ac=get.product.type&AppId="+app.val(),
		
		success: function(msg)
		{
			$("#ProductTypeId").html(msg);
		}
	});
	//*/
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_product">添加产品</a> ]
</fieldset>
<fieldset><legend>产品列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
		<tr class="hover">
			选择游戏
			
			<select name = "AppId" id = "AppId" onchange='getproducttype()'>
			<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>

			选择产品类型
			<select name = "ProductTypeId" id = "ProductTypeId">
			<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
			{tpl:loop $ProductTypeList $App $AppInfo}
			{tpl:if ($App==$AppId)}
			{tpl:loop $AppInfo $key $type}
						<option value = {tpl:$key/} {tpl:if ($key==$ProductTypeId)}selected{/tpl:if}>{tpl:$type.name/}</option>

			{/tpl:loop}
			{/tpl:if}
			{/tpl:loop}
			</select>

<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<tr><th align="center" class="rowtip">产品ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">单价</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">所属分类</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $ProductArr $App $app_data}
	{tpl:loop $app_data $Product $product_data}
<tr>
<td>{tpl:$product_data.ProductId/}</td>
<td>{tpl:$product_data.name/}</td>
<td>{tpl:$product_data.ProductPrice/}</td>
<td>{tpl:$product_data.AppName/}</td>
<td>{tpl:$product_data.ProductTypeName/}</td>
<td><a href="javascript:;" onclick="productModify('{tpl:$product_data.ProductId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$product_data.ProductId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}