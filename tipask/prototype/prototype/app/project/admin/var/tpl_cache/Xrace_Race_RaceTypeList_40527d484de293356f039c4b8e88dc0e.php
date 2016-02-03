<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_app').click(function(){
		addAppBox = divBox.showBox('<?php echo $this->sign; ?>&ac=race.type.add', {title:'添加比赛分类',width:500,height:200});
	});
});

function RaceTypeDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=race.type.delete&RaceTypeId=' + p_id;}});
}

function RaceTypeModify(mid){
	modifyRaceTypeBox = divBox.showBox('<?php echo $this->sign; ?>&ac=race.type.modify&RaceTypeId=' + mid, {title:'修改比赛分类',width:500,height:200});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_app">添加比赛分类</a> ]
</fieldset>

<fieldset><legend>比赛分类列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">比赛分类ID</th>
    <th align="center" class="rowtip">比赛分类名称</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

<?php if (is_array($RaceTypeArr)) { foreach ($RaceTypeArr as $oRaceType) { ?>
  <tr class="hover">
    <td align="center"><?php echo $oRaceType['RaceTypeId']; ?></td>
    <td align="center"><?php echo $oRaceType['RaceTypeName']; ?></td>
    <td align="center"><a  href="javascript:;" onclick="RaceTypeDelete('<?php echo $oRaceType['RaceTypeId']; ?>','<?php echo $oRaceType['RaceTypeName']; ?>')">删除</a> |  <a href="javascript:;" onclick="RaceTypeModify('<?php echo $oRaceType['RaceTypeId']; ?>');">修改</a></td>
  </tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
