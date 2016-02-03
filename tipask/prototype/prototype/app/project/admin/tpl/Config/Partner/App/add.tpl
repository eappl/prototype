{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="partner_add_form" id="partner_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
<table class="table table-bordered table-striped" width="100%">
<tr>
<th><label for="PartnerId">平台</label></th><td>
	<select name="PartnerId" id="PartnerId">
	<option value="partner">请选择</option>
	{tpl:loop $oPratnerArr $partner}<option value="{tpl:$partner.PartnerId/}">{tpl:$partner.name/}</option>{/tpl:loop}
	</select>

</td>
</tr>

<tr>
	<th><label for="product_id">产品名称</label></th><td>
	<select name="AppId" id="AppId">
	{tpl:loop $oAppArr $product}<option value="{tpl:$product.AppId/}" {tpl:if($product.AppId==$AppId)}selected{/tpl:if}>{tpl:$product.name/}</option>{/tpl:loop}
	</select></td>
</tr>
<tr>
<th><label for="IsActive">是否需要激活</label></th><td>
<select name="IsActive" id="IsActive" >
<option value="0" >否</option>
<option value="1" >是</option>
</select>
</td>
</tr>
<tr>
<th><label for="is_official">所在地区</label></th><td>
<select name="AreaId" id="AreaId">
{tpl:loop $AreaList $key $name}
  <option value="{tpl:$key/}">{tpl:$name/}</option>
{/tpl:loop}
</select>
</td>
</tr>
		<tr>
<th><label for="income_rate">当地货币与平台货币的比例</label></th><td>
<input name="coin_rate" type="text" class="span4" id="coin_rate" value="1" />
</td>
</tr>
<tr>
<th><label for="income_type">分成比例方式</label></th><td>
<select name="income_type" id="income_type">
<option value="1">固定比例</option>
<option value="2">分段比例</option>
<option value="3">分段累加</option>
</select>
</td>
</tr>

<tr>
<th><label for="income_rate">收入分成比例</label></th><td>
<input type="text" name="income_rate" id="income_rate" class="span4" /></td></td>
</tr>

<tr>
<th><label for="game_site">官网地址</label></th><td>
<input type="text" name="game_site" id="game_site" class="span4" />  </td>
</tr>

<tr class="noborder"><th></th><td>
<button type="submit" id="partner_add_submit">提交</button></td></tr>
</table>
</form>

<script type="text/javascript">

$(function(){

	$('#partner_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '请选择平台，请修正后再次提交';
					errors[2] = '名称不能为空，请修正后再次提交';
					errors[3] = '支付地址不能为空，请修正后再次提交';
					errors[4] = '充值密钥不能为空，请修正后再次提交';
					errors[5] = '收入分成比例不能为空，请修正后再次提交';
					errors[6] = '收入分成比例格式不正确，请修正后再次提交';
					errors[7] = '官网地址不能为空，请修正后再次提交';
					errors[9] = '添加合作商失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '成功添加一个游戏运营';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.app);}});
				}
			}
		};
		$('#partner_add_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}