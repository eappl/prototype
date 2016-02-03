<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_app').click(function(){
		addAppBox = divBox.showBox('<?php echo $this->sign; ?>&ac=game.group.add', {title:'添加赛事组别',width:500,height:600});
	});
});

function GameGroupDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=game.group.delete&gameGroup=' + p_id;}});
}

function GameGroupModify(mid){
	modifyGameGroupBox = divBox.showBox('<?php echo $this->sign; ?>&ac=game.group.modify&gameGroup=' + mid, {title:'修改赛事组别',width:500,height:600});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_app">添加赛事组别</a> ]
</fieldset>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
  <select name="gameCatalog" size="1">
    <option value="0">全部</option>
    <?php if (is_array($GameCatalogArr)) { foreach ($GameCatalogArr as $oGameCatalog) { ?>
    <option value="<?php echo $oGameCatalog['GameCatalog']; ?>" <?php if($oGameCatalog['GameCatalog']==$gameCatalog) { ?>selected="selected"<?php } ?>><?php echo $oGameCatalog['GameCatalogName']; ?></option>
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

<?php if (is_array($GameGroupArr)) { foreach ($GameGroupArr as $oGameGroup) { ?>
  <tr class="hover">
    <td align="center"><?php echo $oGameGroup['GameCatalogName']; ?></td>
    <td align="center"><?php echo $oGameGroup['GameGroup']; ?></td>
    <td align="center"><?php echo $oGameGroup['GameGroupName']; ?></td>
    <td align="center"><a  href="javascript:;" onclick="GameGroupDelete('<?php echo $oGameGroup['GameGroup']; ?>','<?php echo $oGameGroup['GameGroupName']; ?>')">删除</a> |  <a href="javascript:;" onclick="GameGroupModify('<?php echo $oGameGroup['GameGroup']; ?>');">修改</a></td>
  </tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
