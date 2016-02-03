{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_stage_update_form" name="race_stage_update_form" action="{tpl:$this.sign/}&ac=race.stage.update" metdod="post">
<input type="hidden" name="RaceStageId" id="RaceStageId" value="{tpl:$oRaceStage.RaceStageId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>赛事分站名称</td>
<td align="left"><input name="RaceStageName" type="text" class="span4" id="RaceStageName" value="{tpl:$oRaceStage.RaceStageName/}" size="50" /></td>
</tr>
<tr class="hover"><td>赛事分站Id</td>
<td align="left">{tpl:$oRaceStage.RaceStageId/}</td>
</tr>
<tr class="hover"><td>所属赛事</td>
	<td align="left">	<select name="RaceCatalogId"  id="RaceCatalogId" size="1"  onchange='getGroupList()'>
			<option value="0">全部</option>
			{tpl:loop $RaceCatalogArr $oRaceCatalog}
			<option value="{tpl:$oRaceCatalog.RaceCatalogId/}" {tpl:if($oRaceCatalog.RaceCatalogId==$oRaceStage.RaceCatalogId)}selected="selected"{/tpl:if}>{tpl:$oRaceCatalog.RaceCatalogName/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr>
		<th><label >开停结止时间</label></th>
		<td>
			<input type="text" name="StageStartDate" value="{tpl:$oRaceStage.StageStartDate/}" class="input-medium"
				   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
			---
			<input type="text" name="StageEndDate" value="{tpl:$oRaceStage.StageEndDate/}" value="" class="input-medium"
				   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
		</td>
	</tr>
	<tr>
	<td>赛事分组列表</td>
	<td align="left"><div id = "SelectedGroupList">
			{tpl:loop $RaceGroupArr $oRaceGroup}
			<input type="checkbox"  name="SelectedRaceGroup[{tpl:$oRaceGroup.RaceGroupId/}]" value="{tpl:$oRaceGroup.RaceGroupId/}" {tpl:if($oRaceGroup.selected == 1)}checked{/tpl:if} /> {tpl:$oRaceGroup.RaceGroupName/}
			{/tpl:loop}
		</div></td>
	</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="race_stage_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#race_stage_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事分站名称不能为空，请修正后再次提交';
				errors[2] = '赛事分站ID无效，请修正后再次提交';
				errors[3] = '请选择一个有效的赛事，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改赛事分站成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#race_stage_update_form').ajaxForm(options);
});
function getGroupList()
{
	catalog=$("#RaceCatalogId");
	stage=$("#RaceStageId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=xrace/race.stage&ac=get.selected.group&RaceCatalogId="+catalog.val()+"&RaceStageId="+stage.val(),
		success: function(msg)
		{
			$("#SelectedGroupList").html(msg);
		}
	});
//*/
}
</script>
{tpl:tpl contentFooter/}