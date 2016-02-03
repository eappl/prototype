<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_area').click(function(){
		addAreaBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add', {title:'添加地区',width:600,height:350});
	});
});
function areaModify(p_id){
	modifyAppBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&AreaId=' + p_id, {title:'修改地区',width:600,height:400});
}

function promptDelete(p_id, p_name){
	deleteAreaBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&AreaId=' + p_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_area">添加地区</a> ]
</fieldset>
<fieldset><legend>地区列表</legend>
<table class="table table-bordered table-striped">
<tr><th align="center" class="rowtip">地区ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">国内/国外</th>
<th align="center" class="rowtip">汇率</th><th>操作</th></tr>
<?php if (is_array($areaArr)) { foreach ($areaArr as $area) { ?>
<tr>
<td><?php echo $area['AreaId']; ?></td>
<td><?php echo $area['name']; ?></td>
<td><?php echo $area['abroad']; ?></td>
<td><?php echo sprintf('%3.4f',$area['currency_rate']); ?></td>
<td><a href="javascript:;" onclick="areaModify(<?php echo $area['AreaId']; ?>);">修改</a>
|<a  href="javascript:;" onclick="promptDelete('<?php echo $area['AreaId']; ?>','<?php echo $area['name']; ?>')">删除</a>
</td>
</tr>
<?php } } ?>
</table>
</fieldset>

 
</dl>
<?php include Base_Common::tpl('contentFooter'); ?>