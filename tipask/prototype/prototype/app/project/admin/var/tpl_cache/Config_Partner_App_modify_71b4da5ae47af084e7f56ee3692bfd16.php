<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form name="partner_update_form" id="partner_update_form" action="<?php echo $this->sign; ?>&ac=update" method="post">
<input type="hidden" name="old_parnter_id" value="<?php echo $PartnerId; ?>">
<input type="hidden" name="old_AppId" value="<?php echo $AppId; ?>">
<table class="table table-bordered table-striped" width="100%">

<tr>
<th><label for="PartnerId" >平台</label></th><td>
	<select name="PartnerId" id="PartnerId">
	<option value="partner">请选择</option>
	<?php if (is_array($partnerArr)) { foreach ($partnerArr as $part) { ?>
		<option value="<?php echo $part['PartnerId']; ?>" <?php if($part['PartnerId'] == $partner['PartnerId']) { ?>selected<?php } ?> ><?php echo $part['name']; ?></option>
	<?php } } ?>
	</select>

</td>
</tr>

<tr>
	<th><label for="product_id">产品名称</label></th><td>
	<select name="AppId" id="AppId" >
	<?php if (is_array($productArr)) { foreach ($productArr as $product) { ?>
		<option value="<?php echo $product['AppId']; ?>" <?php if($product['AppId']==$partner['AppId']) { ?>selected<?php } ?> ><?php echo $product['name']; ?></option>
	<?php } } ?>
	</select></td>
</tr>
<tr>
<th><label for="IsActive">是否需要激活</label></th><td>
<select name="IsActive" id="IsActive" >
<option value="0" <?php if($partner['IsActive']==0) { ?>selected<?php } ?>>否</option>
<option value="1" <?php if($partner['IsActive']==1) { ?>selected<?php } ?>>是</option>
</select>
</td>
</tr>
<tr>
<tr>
<th><label for="is_official">所在地区</label></th><td>
<select name="AreaId" id="AreaId">
<?php if (is_array($AreaList)) { foreach ($AreaList as $key => $name) { ?>
  <option value="<?php echo $key; ?>"<?php if($key==$partner['AreaId']) { ?> selected="selected"<?php } ?>><?php echo $name; ?></option>
<?php } } ?>
</select>
</td>
</tr>
<tr>
<th><label for="income_rate">当地货币与平台货币的比例</label></th><td>
<input type="text" name="coin_rate" id="coin_rate" class="span4" value="<?php echo $partner['coin_rate']; ?>"/></td>
</tr>
<tr>
<th><label for="income_type">分成比例方式</label></th><td>
<select name="income_type" id="income_type" onchange="if(this.value != '') setIncomeDesc(this.options[this.selectedIndex].value);">
<option value="1" <?php if($partner['income_type']==1) { ?>selected<?php } ?>>固定比例</option>
<option value="2" <?php if($partner['income_type']==2) { ?>selected<?php } ?>>分段比例</option>
<option value="3" <?php if($partner['income_type']==3) { ?>selected<?php } ?>>分段累加</option>
</select>
</td>
</tr>

<tr>
<th><label for="income_rate">收入分成比例</label></th><td>
<input type="text" name="income_rate" id="income_rate" class="span4" value="<?php echo $partner['income_rate']; ?>"/></td>
</tr>

<tr>
<th><label for="game_site">官网地址</label></th><td>
<input type="text" name="game_site" id="game_site" class="span4" value="<?php echo $partner['game_site']; ?>"/>  </td>
</tr>
<tr>
<th><label for="game_site">其它内容json格式保存</label></th><td>
<textarea name="comment" id="comment" cols="45" rows="5"><?php echo $partner['comment']; ?></textarea>
</td>
</tr>
<tr class="noborder"><th></th><td>
<button type="submit" id="partner_update_submit">提交</button></td></tr>
</table>
</form>

<script type="text/javascript">


$(function(){

	$('#partner_update_submit').click(function(){
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
					var message = '成功修改一个游戏运营';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>'+ '&AppId=' + jsonResponse.app);}});

				}
			}
		};
		$('#partner_update_form').ajaxForm(options);
	});
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>