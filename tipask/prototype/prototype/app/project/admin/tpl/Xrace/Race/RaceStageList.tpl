{tpl:tpl contentHeader/}
<script type="text/javascript">
  $(document).ready(function(){
    $('#add_race_stage').click(function(){
      addRaceStageBox= divBox.showBox('{tpl:$this.sign/}&ac=race.stage.add', {title:'添加赛事分站',width:500,height:600});
    });
  });
  function RaceStageModify(mid){
    modifyRaceStageBox = divBox.showBox('{tpl:$this.sign/}&ac=race.stage.modify&RaceStageId=' + mid, {title:'修改赛事分站',width:800,height:600});
  }
  function RaceStageDelete(p_id, p_name){
    deleteRaceStageBox= divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=race.stage.delete&RaceStageId=' + p_id;}});
  }


</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_race_stage">添加赛事分站</a> ]
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
<fieldset><legend>赛事分站列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">对应赛事</th>
    <th align="center" class="rowtip">赛事分站ID</th>
    <th align="center" class="rowtip">赛事分站名称</th>
    <th align="center" class="rowtip">开始日期</th>
    <th align="center" class="rowtip">结束日期</th>
    <th align="center" class="rowtip">已开设组别</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

  {tpl:loop $RaceStageList $RaceCatalogId $oRaceCatalogInfo}
  <tr>
    <th align="center" class="rowtip"  rowspan = {tpl:$oRaceCatalogInfo.RowCount /}>{tpl:$oRaceCatalogInfo.RaceCatalogName/}</th>
  </tr>
  {tpl:loop $oRaceCatalogInfo.RaceStageList $RaceStageId $oRaceStage}
  <tr>
    <th align="center" class="rowtip" >{tpl:$oRaceStage.RaceStageId/}</th>
    <th align="center" class="rowtip" >{tpl:$oRaceStage.RaceStageName/}</th>
    <th align="center" class="rowtip" >{tpl:$oRaceStage.StageStartDate/}</th>
    <th align="center" class="rowtip" >{tpl:$oRaceStage.StageEndDate/}</th>
    <td align="center" class="rowtip" >{tpl:$oRaceStage.SelectedGroupList/}</td>
    <th align="center" class="rowtip" ><a  href="javascript:;" onclick="RaceGroupDelete('{tpl:$oRaceStage.RaceStageId/}','{tpl:$oRaceGroup.RaceStageName/}')">删除</a> |  <a href="javascript:;" onclick="RaceStageModify('{tpl:$oRaceStage.RaceStageId/}');">修改</a></th></tr>
  </tr>
  {/tpl:loop}
  {/tpl:loop}


</table>
</fieldset>
{tpl:tpl contentFooter/}
