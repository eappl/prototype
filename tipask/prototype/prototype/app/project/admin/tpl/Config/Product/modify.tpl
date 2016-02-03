{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script type="text/javascript">
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

<form name="product_modify_form" id="product_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<fieldset><legend>修改产品</legend>

		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>产品ID</td>
<td>{tpl:$Product.ProductId/}</td>
</tr>

		<input type="hidden" name="ProductId" id="ProductId" class="span4" value="{tpl:$Product.ProductId/}"/>
		<input type="hidden" name="oldAppId" id="oldAppId" class="span4" value="{tpl:$Product.AppId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$Product.name/}"/></td>
</tr>
<td>单价</td>
<td><input type="text" name="ProductPrice" id="ProductPrice" class="span4"   size="50" value="{tpl:$Product.ProductPrice/}"/></td>
</tr>

		<tr class="hover">
			<td>选择游戏</td>
			<td align="left">
			<select name = "AppId" id = "AppId" onchange = "getproducttype()">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$Product.AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>

		<tr class="hover">
			<td>选择产品分类</td>
			<td align="left">
			<select name = "ProductTypeId" id = "ProductTypeId">
			<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
			{tpl:loop $ProductTypeList $App $AppInfo}
			{tpl:if ($App==$AppId)}
			{tpl:loop $AppInfo $key $type}
						<option value = {tpl:$key/} {tpl:if ($key==$Product.ProductTypeId)}selected{/tpl:if}>{tpl:$type.name/}</option>

			{/tpl:loop}
			{/tpl:if}
			{/tpl:loop}
</td>
		</tr>
		
		<tr class="noborder"><td></td>
		<td><button type="submit" id="product_modify_submit">提交</button></td>
		</tr>
</table>
	</fieldset>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#product_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个游戏';
					errors[2] = '失败，必须输入产品名称';
					errors[3] = '失败，必须输入产品ID';
					errors[4] = '失败，必须输入正确的价格';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改产品成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId + '&ProductTypeId=' + jsonResponse.ProductTypeId);}});
				}
			}
		};
		$('#product_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}