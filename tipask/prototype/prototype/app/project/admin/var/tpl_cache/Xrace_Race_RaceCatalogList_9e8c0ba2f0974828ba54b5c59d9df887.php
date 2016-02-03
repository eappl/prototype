<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_app').click(function(){
		addAppBox = divBox.showBox('<?php echo $this->sign; ?>&ac=race.catalog.add', {title:'添加赛事',width:500,height:600});
	});
});

function RaceCatalogDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=race.catalog.delete&raceCatalogId=' + p_id;}});
}

function RaceCatalogModify(mid){
	modifyRaceCatalogBox = divBox.showBox('<?php echo $this->sign; ?>&ac=race.catalog.modify&raceCatalogId=' + mid, {title:'修改赛事',width:500,height:600});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_app">添加赛事</a> ]
</fieldset>

<fieldset><legend>赛事列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">赛事ID</th>
    <th align="center" class="rowtip">赛事名称</th>
    <th align="center" class="rowtip">赛事图标</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

<?php if (is_array($RaceCatalogArr)) { foreach ($RaceCatalogArr as $oRaceCatalog) { ?>
  <tr class="hover">
    <td align="center"><?php echo $oRaceCatalog['RaceCatalogId']; ?></td>
    <td align="center"><?php echo $oRaceCatalog['RaceCatalogName']; ?></td>
    <td align="center"><?php if($oRaceCatalog['comment']['RaceCatalogIcon_root']=="") { ?>未定义<?php } else { ?><img src="<?php echo $RootUrl; ?><?php echo $oRaceCatalog['comment']['RaceCatalogIcon_root']; ?>" width='150' height='130'/><?php } ?></td>    </td>
    <td align="center"><a  href="javascript:;" onclick="RaceCatalogDelete('<?php echo $oRaceCatalog['RaceCatalogId']; ?>','<?php echo $oRaceCatalog['RaceCatalogName']; ?>')">删除</a> |  <a href="javascript:;" onclick="RaceCatalogModify('<?php echo $oRaceCatalog['RaceCatalogId']; ?>');">修改</a></td>
  </tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
