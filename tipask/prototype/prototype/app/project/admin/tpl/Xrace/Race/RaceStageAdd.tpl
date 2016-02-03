{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_stage_add_form" name="race_stage_add_form"" action="{tpl:$this.sign/}&ac=race.stage.insert" metdod="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>赛事分站名称</td>
	<td align="left"><input type="text" class="span4" name="RaceStageName"  id="RaceStageName" value="" size="50" /></td>
</tr>
<tr class="hover"><td>所属赛事</td>
	<td align="left">	<select name="RaceCatalogId" id="RaceCatalogId" size="1" onchange="getGroupList()">
			<option value="0">全部</option>
			{tpl:loop $RaceCatalogArr $oRaceCatalog}
			<option value="{tpl:$oRaceCatalog.RaceCatalogId/}" >{tpl:$oRaceCatalog.RaceCatalogName/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<td>赛事分组列表</td>
	<td align="left"><div id = "SelectedGroupList"></div></td>
	</tr>
	<td>赛事分组列表</td>
	<td align="left"><?php echo $editor->editor("Answer",""); ?></td>
	</tr>

<tr class="noborder"><td></td>
<td><button type="submit" id="race_stage_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#race_stage_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事分站名称不能为空，请修正后再次提交';
				errors[3] = '请选择一个有效的赛事，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加赛事分站成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#race_stage_add_form').ajaxForm(options);
});
function getGroupList()
{
	catalog=$("#RaceCatalogId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=xrace/race.stage&ac=get.selected.group&RaceCatalogId="+catalog.val(),
		success: function(msg)
		{
			$("#SelectedGroupList").html(msg);
		}
	});
//*/
}
</script>
{tpl:tpl contentFooter/}