{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_group_update_form" name="race_group_update_form" action="{tpl:$this.sign/}&ac=race.group.update" metdod="post">
<input type="hidden" name="RaceGroupId" value="{tpl:$oRaceGroup.RaceGroupId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>赛事组别名称</td>
<td align="left"><input name="RaceGroupName" type="text" class="span4" id="RaceGroupName" value="{tpl:$oRaceGroup.RaceGroupName/}" size="50" /></td>
</tr>
<tr class="hover"><td>所属赛事</td>
<td align="left">	<select name="RaceCatalogId" size="1">
<option value="0">全部</option>
{tpl:loop $RaceCatalogArr $oRaceCatalog}
<option value="{tpl:$oRaceCatalog.RaceCatalogId/}" {tpl:if($oRaceCatalog.RaceCatalogId==$oRaceGroup.RaceCatalogId)}selected="selected"{/tpl:if}>{tpl:$oRaceCatalog.RaceCatalogName/}</option>
{/tpl:loop}
</select></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="race_group_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#race_group_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事组别名称不能为空，请修正后再次提交';
				errors[2] = '赛事组别ID无效，请修正后再次提交';
				errors[3] = '请选择一个有效的赛事，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改赛事组别成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#race_group_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}