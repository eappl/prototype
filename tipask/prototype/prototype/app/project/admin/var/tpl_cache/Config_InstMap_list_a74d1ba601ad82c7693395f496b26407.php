<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_instmap').click(function(){
		addInstMapBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add', {title:'添加副本', width:600, height:300});
	});
});
function instmapModify(m_id,p_id){
	modifyInstMapBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&InstMapId=' + m_id + '&AppId=' + p_id, {title:'修改副本', width:600, height:300});
}

function promptDelete(m_id,p_id){
	deleteInstMapBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&AppId=' + p_id + '&InstMapId=' + m_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_instmap">添加副本</a> ]
</fieldset>
<fieldset><legend>副本列表</legend>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
		<tr class="hover">
			选择游戏
			<select name = "AppId" id = "AppId">
			<?php if (is_array($AppList)) { foreach ($AppList as $key => $app) { ?>
			<option value = <?php echo $key; ?> <?php if($key==$AppId) { ?>selected<?php } ?>><?php echo $app['name']; ?></option>
			<?php } } ?>
			</select>
<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<table class="table table-bordered table-striped">
<tr>
<th align="center" class="rowtip">副本ID</th>
<th align="center" class="rowtip">副本名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th>
</tr>
<?php if (is_array($InstMapArr)) { foreach ($InstMapArr as $App => $app_data) { ?>
	<?php if (is_array($app_data)) { foreach ($app_data as $InstMap => $instmap_data) { ?>
<tr>
<td><?php echo $instmap_data['InstMapId']; ?></td>
<td><?php echo $instmap_data['name']; ?></td>
<td><?php echo $instmap_data['AppName']; ?></td>
<td><a href="javascript:;" onclick="instmapModify('<?php echo $instmap_data['InstMapId']; ?>','<?php echo $App; ?>');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('<?php echo $instmap_data['InstMapId']; ?>','<?php echo $App; ?>')">删除</a>
</td>
</tr>
	<?php } } ?>
<?php } } ?>
</table>
</fieldset>

 
</dl>
<?php include Base_Common::tpl('contentFooter'); ?>