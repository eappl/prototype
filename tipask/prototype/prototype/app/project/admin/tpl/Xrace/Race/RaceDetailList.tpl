{tpl:tpl contentHeader/}
<script type="text/javascript">
  $('#return_to_stage').click(function(){
    stage=$("#RaceStageId");
    alert(1);
    location.href = '{tpl:$this.sign/}&raceStageId=' + stage;
    alert(location.href);
  });

</script>

<form action="{tpl:$this.sign/}&ac=race.detail.update" name="form" id="form" method="post">
<input type="hidden" name="RaceStageId" id="RaceStageId" value="{tpl:$oRaceStage.RaceStageId/}" />

  <fieldset><legend>{tpl:$oRaceStage.RaceStageName/} 赛段详情列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
    <th align="center" class="rowtip">对应赛事</th>
    {tpl:loop $menuArr $oMenuId $oMenuName}
      <th align="center" class="rowtip">{tpl:$oMenuName/}</th>
    {/tpl:loop}
  </tr>

{tpl:loop $oRaceStage.comment.SelectedGroupDetail $oRaceGroupId $oRaceGroupInfo}
  <tr><th align="center" class="rowtip" rowspan = {tpl:$MaxRaceDetail /}>{tpl:$oRaceGroupInfo.RaceGroupName/}</th></tr>
    {tpl:loop $oRaceGroupInfo.DetailList $oRaceDetailId $oRaceDetailInfo}
      {tpl:loop $oRaceDetailInfo $k $v}
      <th align="center" class="rowtip" ><input name="SelectedGroupDetail[{tpl:$oRaceGroupId/}][{tpl:$oRaceDetailId/}][{tpl:$k/}]" type="text" class="span3" id="DetailList[{tpl:$oRaceGroupId/}][{tpl:$oRaceDetailId/}][{tpl:$k/}]" value="{tpl:$v/}"  size="30"/></th>
      {/tpl:loop}
      </tr>
    {/tpl:loop}
{/tpl:loop}
  <tr><td align="center" colspan = 4><button type="submit">提交</button> <button type="button"  id="return_to_stage" name="return_to_stage" >返回</button></td></tr>

</table>
</fieldset>

</form>
{tpl:tpl contentFooter/}
