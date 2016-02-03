<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form id="game_group_add_form" name="game_group_add_form"" action="<?php echo $this->sign; ?>&ac=game.group.insert" metdod="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>赛事组别名称</td>
	<td align="left"><input type="text" class="span4" name="GameGroupName"  id="GameGroupName" value="" size="50" /></td>
</tr>
<tr class="hover"><td>赛事组别ID</td>
	<td align="left"><input type="text" class="span4" name="GameGroup" ="GameGroup" value="" size="50" /></td>
</tr>
	<tr class="hover"><td>所属赛事</td>
		<td align="left">	<select name="gameCatalog" size="1">
				<option value="0">全部</option>
				<?php if (is_array($GameCatalogArr)) { foreach ($GameCatalogArr as $oGameCatalog) { ?>
				<option value="<?php echo $oGameCatalog['GameCatalog']; ?>" ><?php echo $oGameCatalog['GameCatalogName']; ?></option>
				<?php } } ?>
			</select></td>
	</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="game_group_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#game_group_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事组别名称不能为空，请修正后再次提交';
				errors[2] = '赛事组别不能为空，请修正后再次提交';
				errors[3] = '请选择一个有效的赛事，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加赛事组别成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
			}
		}
	};
	$('#game_group_add_form').ajaxForm(options);
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>