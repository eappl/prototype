{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_app').click(function(){
		addAppBox = divBox.showBox('{tpl:$this.sign/}&ac=race.group.add', {title:'添加赛事组别',width:500,height:300});
	});
});

function RaceGroupDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=race.group.delete&raceGroupId=' + p_id;}});
}

function RaceGroupModify(mid){
	modifyRaceGroupBox = divBox.showBox('{tpl:$this.sign/}&ac=race.group.modify&raceGroupId=' + mid, {title:'修改赛事组别',width:500,height:300});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_app">添加赛事组别</a> ]
</fieldset>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
  <select name="RaceCatalogId" size="1">
    <option value="0">全部</option>
    {tpl:loop $RaceCatalogArr $oRaceCatalog}
    <option value="{tpl:$oRaceCatalog.RaceCatalogId/}" {tpl:if($oRaceCatalog.RaceCatalogId==$RaceCatalogId)}selected="selected"{/tpl:if}>{tpl:$oRaceCatalog.RaceCatalogName/}</option>
    {/tpl:loop}
  </select>
  <input type="submit" name="Submit" value="查询" />
</form>
<fieldset><legend>赛事组别列表 </legend>
  <table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">对应赛事</th>
    <th align="center" class="rowtip">赛事组别ID</th>
    <th align="center" class="rowtip">赛事组别名称</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $RaceGroupList $RaceCatalogId $oRaceCatalogInfo}
  <tr>
    <th align="center" class="rowtip"  rowspan = {tpl:$oRaceCatalogInfo.RowCount /}>{tpl:$oRaceCatalogInfo.RaceCatalogName/}</th>
  </tr>
  {tpl:loop $oRaceCatalogInfo.RaceGroupList $RaceGroupId $oRaceGroup}
  <tr>
    <th align="center" class="rowtip" >{tpl:$oRaceGroup.RaceGroupId/}</th>
    <th align="center" class="rowtip" >{tpl:$oRaceGroup.RaceGroupName/}</th>
    <th align="center" class="rowtip" ><a  href="javascript:;" onclick="RaceGroupDelete('{tpl:$oRaceGroup.RaceGroupId/}','{tpl:$oRaceGroup.RaceGroupName/}')">删除</a> |  <a href="javascript:;" onclick="RaceGroupModify('{tpl:$oRaceGroup.RaceGroupId/}');">修改</a></th>
  </tr>
  {/tpl:loop}
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}
