<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form id="game_catalog_add_form" name="game_catalog_add_form" action="<?php echo $this->sign; ?>&ac=game.catalog.insert" metdod="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>赛事名称</td>
	<td align="left"><input type="text" class="span4" name="GameCatalogName"  id="GameCatalogName" value="" size="50" /></td>
</tr>
<tr class="hover"><td>赛事ID</td>
	<td align="left"><input type="text" class="span4" name="GameCatalog" ="GameCatalog" value="" size="50" /></td>
</tr>
<tr class="hover"><td>赛事图标</td>
	<td align="left"><input name="GameCatalogIcon[1]" type="file" class="span4" id="GameCatalogIcon[1]" /></td>
</tr>
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
				errors[1] = '赛事名称不能为空，请修正后再次提交';
				errors[2] = '赛事不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加赛事成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
			}
		}
	};
	$('#game_catalog_add_form').ajaxForm(options);
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>