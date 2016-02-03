<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
  $('#return_to_stage').click(function(){
    stage=$("#RaceStageId");
    alert(1);
    location.href = '<?php echo $this->sign; ?>&raceStageId=' + stage;
    alert(location.href);
  });

</script>

<form action="<?php echo $this->sign; ?>&ac=race.detail.update" name="form" id="form" method="post">
<input type="hidden" name="RaceStageId" id="RaceStageId" value="<?php echo $oRaceStage['RaceStageId']; ?>" />

  <fieldset><legend><?php echo $oRaceStage['RaceStageName']; ?> 赛段详情列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
    <th align="center" class="rowtip">对应赛事</th>
    <?php if (is_array($menuArr)) { foreach ($menuArr as $oMenuId => $oMenuName) { ?>
      <th align="center" class="rowtip"><?php echo $oMenuName; ?></th>
    <?php } } ?>
  </tr>

<?php if (is_array($oRaceStage['comment']['SelectedGroupDetail'])) { foreach ($oRaceStage['comment']['SelectedGroupDetail'] as $oRaceGroupId => $oRaceGroupInfo) { ?>
  <tr><th align="center" class="rowtip" rowspan = <?php echo $MaxRaceDetail; ?>><?php echo $oRaceGroupInfo['RaceGroupName']; ?></th></tr>
    <?php if (is_array($oRaceGroupInfo['DetailList'])) { foreach ($oRaceGroupInfo['DetailList'] as $oRaceDetailId => $oRaceDetailInfo) { ?>
      <?php if (is_array($oRaceDetailInfo)) { foreach ($oRaceDetailInfo as $k => $v) { ?>
      <th align="center" class="rowtip" ><input name="SelectedGroupDetail[<?php echo $oRaceGroupId; ?>][<?php echo $oRaceDetailId; ?>][<?php echo $k; ?>]" type="text" class="span3" id="DetailList[<?php echo $oRaceGroupId; ?>][<?php echo $oRaceDetailId; ?>][<?php echo $k; ?>]" value="<?php echo $v; ?>"  size="30"/></th>
      <?php } } ?>
      </tr>
    <?php } } ?>
<?php } } ?>
  <tr><td align="center" colspan = 4><button type="submit">提交</button> <button type="button"  id="return_to_stage" name="return_to_stage" >返回</button></td></tr>

</table>
</fieldset>

</form>
<?php include Base_Common::tpl('contentFooter'); ?>
