<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form id="race_type_update_form" name="race_type_update_form" action="<?php echo $this->sign; ?>&ac=race.type.update" metdod="post">
<input type="hidden" name="RaceTypeId" value="<?php echo $oRaceType['RaceTypeId']; ?>" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>比赛类型名称</td>
<td align="left"><input name="RaceTypeName" type="text" class="span4" id="RaceTypeName" value="<?php echo $oRaceType['RaceTypeName']; ?>" size="50" /></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="race_type_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#race_type_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '比赛类型名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改比赛类型成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
			}
		}
	};
	$('#race_type_update_form').ajaxForm(options);
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>