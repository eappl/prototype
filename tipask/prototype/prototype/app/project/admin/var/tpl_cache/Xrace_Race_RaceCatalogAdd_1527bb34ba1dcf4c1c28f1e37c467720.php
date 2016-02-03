<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form id="race_catalog_add_form" name="race_catalog_add_form" action="<?php echo $this->sign; ?>&ac=race.catalog.insert" metdod="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>赛事名称</td>
	<td align="left"><input type="text" class="span4" name="RaceCatalogName"  id="RaceCatalogName" value="" size="50" /></td>
</tr>
<tr class="hover"><td>赛事图标</td>
	<td align="left"><input name="RaceCatalogIcon[1]" type="file" class="span4" id="RaceCatalogIcon[1]" /></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="race_catalog_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#race_catalog_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加赛事成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
			}
		}
	};
	$('#race_catalog_add_form').ajaxForm(options);
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>