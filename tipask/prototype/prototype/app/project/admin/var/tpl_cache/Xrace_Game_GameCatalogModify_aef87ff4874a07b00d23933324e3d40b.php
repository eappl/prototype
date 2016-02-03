<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form id="game_catalog_update_form" name="game_catalog_update_form" action="<?php echo $this->sign; ?>&ac=game.catalog.update" metdod="post">
<input type="hidden" name="GameCatalog" value="<?php echo $oGameCatalog['GameCatalog']; ?>" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>赛事名称</td>
<td align="left"><input name="GameCatalogName" type="text" class="span4" id="GameCatalogName" value="<?php echo $oGameCatalog['GameCatalogName']; ?>" size="50" /></td>
</tr>
<tr class="hover"><td>赛事</td>
<td align="left"><?php echo $oGameCatalog['GameCatalog']; ?></td>
</tr>
	<tr class="hover"><td>赛事图标</td>
		<td align="left"><input name="GameCatalogIcon[1]" type="file" class="span4" id="GameCatalogIcon[1]" /></td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="game_catalog_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#game_catalog_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改赛事成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
			}
		}
	};
	$('#game_catalog_update_form').ajaxForm(options);
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>