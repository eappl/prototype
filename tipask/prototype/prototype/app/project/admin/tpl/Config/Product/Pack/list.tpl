{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_product_pack').click(function(){
		addProductPackBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加产品包', width:600, height:600});
	});
});
function productPackModify(m_id){
	modifyProductPackBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&ProductPackId=' + m_id, {title:'修改产品包', width:600, height:600});
}

function promptDelete(m_id,m_name){
	deleteProductPackBox = divBox.confirmBox({content:'是否删除 '+ m_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&ProductPackId=' + m_id;}});
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
[ <a href="javascript:;" id="add_product_pack">添加产品包</a> ]
</fieldset>
<fieldset><legend>产品列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
		<tr class="hover">
			选择游戏			
			<select name = "AppId" id = "AppId">
			<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>

<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<tr><th align="center" class="rowtip">产品包ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">单价</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">产品包内容</th>
<th align="center" class="rowtip">使用次数限制</th>
<th align="center" class="rowtip">发放次数限制</th>
<th align="center" class="rowtip">使用时间间隔</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $ProductPackArr $App $app_data}
	{tpl:loop $app_data $ProductPack $productpack_data}
<tr>
<td>{tpl:$productpack_data.ProductPackId/}</td>
<td>{tpl:$productpack_data.name/}</td>
<td>{tpl:$productpack_data.ProductPrice/}</td>
<td>{tpl:$productpack_data.AppName/}</td>
<td>{tpl:$productpack_data.ProductListText/}</td>
<td>{tpl:if ($productpack_data.UseCountLimit==0)}不限制{tpl:else}{tpl:$productpack_data.UseCountLimit/}次{/tpl:if}</td>
<td>{tpl:if ($productpack_data.AsignCountLimit==0)}不限制{tpl:else}{tpl:$productpack_data.AsignCountLimit/}次{/tpl:if}</td>
<td>{tpl:if ($productpack_data.UseTimeLag==0)}不限制{tpl:else}{tpl:$productpack_data.UseTimeLag/}秒{/tpl:if}</td>
<td><a href="javascript:;" onclick="productPackModify('{tpl:$productpack_data.ProductPackId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$productpack_data.ProductPackId/}','{tpl:$productpack_data.name/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}