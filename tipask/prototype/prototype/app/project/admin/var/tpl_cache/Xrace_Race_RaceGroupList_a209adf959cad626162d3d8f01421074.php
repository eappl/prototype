<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_app').click(function(){
		addAppBox = divBox.showBox('<?php echo $this->sign; ?>&ac=race.group.add', {title:'添加赛事组别',width:500,height:300});
	});
});

function RaceGroupDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=race.group.delete&raceGroupId=' + p_id;}});
}

function RaceGroupModify(mid){
	modifyRaceGroupBox = divBox.showBox('<?php echo $this->sign; ?>&ac=race.group.modify&raceGroupId=' + mid, {title:'修改赛事组别',width:500,height:300});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_app">添加赛事组别</a> ]
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
<fieldset><legend>赛事组别列表 </legend>
  <table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">对应赛事</th>
    <th align="center" class="rowtip">赛事组别ID</th>
    <th align="center" class="rowtip">赛事组别名称</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

<?php if (is_array($RaceGroupList)) { foreach ($RaceGroupList as $RaceCatalogId => $oRaceCatalogInfo) { ?>
  <tr>
    <th align="center" class="rowtip"  rowspan = <?php echo $oRaceCatalogInfo['RowCount']; ?>><?php echo $oRaceCatalogInfo['RaceCatalogName']; ?></th>
  </tr>
  <?php if (is_array($oRaceCatalogInfo['RaceGroupList'])) { foreach ($oRaceCatalogInfo['RaceGroupList'] as $RaceGroupId => $oRaceGroup) { ?>
  <tr>
    <th align="center" class="rowtip" ><?php echo $oRaceGroup['RaceGroupId']; ?></th>
    <th align="center" class="rowtip" ><?php echo $oRaceGroup['RaceGroupName']; ?></th>
    <th align="center" class="rowtip" ><a  href="javascript:;" onclick="RaceGroupDelete('<?php echo $oRaceGroup['RaceGroupId']; ?>','<?php echo $oRaceGroup['RaceGroupName']; ?>')">删除</a> |  <a href="javascript:;" onclick="RaceGroupModify('<?php echo $oRaceGroup['RaceGroupId']; ?>');">修改</a></th>
  </tr>
  <?php } } ?>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
