<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
  $(document).ready(function(){
    $('#add_race_stage').click(function(){
      addRaceStageBox= divBox.showBox('<?php echo $this->sign; ?>&ac=race.stage.add', {title:'添加赛事分站',width:500,height:600});
    });
  });
  function RaceStageModify(mid){
    modifyRaceStageBox = divBox.showBox('<?php echo $this->sign; ?>&ac=race.stage.modify&RaceStageId=' + mid, {title:'修改赛事分站',width:800,height:600});
  }
  function RaceStageDelete(p_id, p_name){
    deleteRaceStageBox= divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=race.stage.delete&RaceStageId=' + p_id;}});
  }


</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_race_stage">添加赛事分站</a> ]
</fieldset>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
  <select name="RaceCatalogId" size="1">
    <option value="0">全部</option>
    <?php if (is_array($RaceCatalogArr)) { foreach ($RaceCatalogArr as $oRaceCatalog) { ?>
    <option value="<?php echo $oRaceCatalog['RaceCatalogId']; ?>" <?php if($oRaceCatalog['RaceCatalogId']==$RaceCatalogId) { ?>selected="selected"<?php } ?>><?php echo $oRaceCatalog['RaceCatalogName']; ?></option>
    <?php } } ?>
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

  <?php if (is_array($RaceStageList)) { foreach ($RaceStageList as $RaceCatalogId => $oRaceCatalogInfo) { ?>
  <tr>
    <th align="center" class="rowtip"  rowspan = <?php echo $oRaceCatalogInfo['RowCount']; ?>><?php echo $oRaceCatalogInfo['RaceCatalogName']; ?></th>
  </tr>
  <?php if (is_array($oRaceCatalogInfo['RaceStageList'])) { foreach ($oRaceCatalogInfo['RaceStageList'] as $RaceStageId => $oRaceStage) { ?>
  <tr>
    <th align="center" class="rowtip" ><?php echo $oRaceStage['RaceStageId']; ?></th>
    <th align="center" class="rowtip" ><?php echo $oRaceStage['RaceStageName']; ?></th>
    <th align="center" class="rowtip" ><?php echo $oRaceStage['StageStartDate']; ?></th>
    <th align="center" class="rowtip" ><?php echo $oRaceStage['StageEndDate']; ?></th>
    <td align="center" class="rowtip" ><?php echo $oRaceStage['SelectedGroupList']; ?></td>
    <th align="center" class="rowtip" ><a  href="javascript:;" onclick="RaceGroupDelete('<?php echo $oRaceStage['RaceStageId']; ?>','<?php echo $oRaceGroup['RaceStageName']; ?>')">删除</a> |  <a href="javascript:;" onclick="RaceStageModify('<?php echo $oRaceStage['RaceStageId']; ?>');">修改</a></th></tr>
  </tr>
  <?php } } ?>
  <?php } } ?>


</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
