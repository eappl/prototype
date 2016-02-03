<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form id="sports_type_add_form" name="sports_type_add_form" action="<?php echo $this->sign; ?>&ac=sports.type.insert" metdod="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>运动类型名称</td>
	<td align="left"><input type="text" class="span4" name="SportsTypeName"  id="SportsTypeName" value="" size="50" /></td>
</tr>
<tr class="hover"><th align="center" class="rowtip"  colspan = 2>自定义参数列表</td></tr>
</tr>
<?php if (is_array($oSportsType['comment']['params'])) { foreach ($oSportsType['comment']['params'] as $oParamsId => $oParamsInfo) { ?>
<tr class="hover">
	<th align="center" class="rowtip" >参数名<input name="ParamsInfo[<?php echo $oParamsId; ?>][paramName]" type="text" class="span4" id="ParamsInfo[<?php echo $oParamsId; ?>][paramName]" value="<?php echo $oParamsInfo['paramName']; ?>" size="50" /></th>
	<th align="center" class="rowtip" >参数<input name="ParamsInfo[<?php echo $oParamsId; ?>][param]" type="text" class="span4" id="ParamsInfo[<?php echo $oParamsId; ?>][param]" value="<?php echo $oParamsInfo['param']; ?>" size="50" /></th>
</tr>
<?php } } ?>
	<tr class="noborder"><td></td>
<td><button type="submit" id="app_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#app_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '运动类型名称不能为空，请修正后再次提交';
				errors[2] = '运动类型不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加运动类型成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
			}
		}
	};
	$('#sports_type_add_form').ajaxForm(options);
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>