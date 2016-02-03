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
<form id="product_add_form" name="product_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

		<tr class="hover">
			<td>产品ID</td>
			<td align="left"><input name="ProductId" type="text" class="span4" id="ProductId" value="" size="50" /></td>
		</tr>
		
		<tr class="hover">
			<td>产品名称</td>
			<td align="left"><input name="name" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>
		
		<tr class="hover">
			<td>产品价格</td>
			<td align="left"><input name="ProductPrice" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>

		<tr class="hover">
			<td>选择游戏</td>
			<td align="left">
			<select name = "AppId" id = "AppId" onchange = "getproducttype()">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} >{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>
		<tr class="hover">
			<td>选择产品分类</td>
			<td align="left">
			<select name = "ProductTypeId" id = "ProductTypeId">
			<option value = 0 > 全部 </option>

		</td>
		</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="product_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('name').focus();
$('#product_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
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
				var message = '添加产品成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId + '&ProductTypeId=' + jsonResponse.ProductTypeId);}});
			}
		}
	};
	$('#product_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}

