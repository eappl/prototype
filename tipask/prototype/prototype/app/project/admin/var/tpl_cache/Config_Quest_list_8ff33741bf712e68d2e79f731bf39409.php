<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_quest').click(function(){
		addQuestBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add', {title:'添加任务', width:600, height:300});
	});
});
function questModify(m_id,p_id){
	modifyQuestBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&QuestId=' + m_id + '&AppId=' + p_id, {title:'修改任务', width:600, height:300});
}

function promptDelete(m_id,p_id){
	deleteQuestBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&AppId=' + p_id + '&InstMapId=' + m_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_quest">添加任务</a> ]
</fieldset>
<fieldset><legend>任务列表</legend>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
选择游戏	<select name = "AppId" id = "AppId">
			<?php if (is_array($AppList)) { foreach ($AppList as $key => $app) { ?>
			<option value = <?php echo $key; ?> <?php if($key==$AppId) { ?>selected<?php } ?>><?php echo $app['name']; ?></option>
			<?php } } ?>
			</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">
<tr>
<th align="center" class="rowtip">任务ID</th>
<th align="center" class="rowtip">任务名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th></tr>
<?php if (is_array($QuestArr)) { foreach ($QuestArr as $App => $app_data) { ?>
	<?php if (is_array($app_data)) { foreach ($app_data as $Quest => $quest_data) { ?>
<tr>
<td><?php echo $quest_data['QuestId']; ?></td>
<td><?php echo $quest_data['name']; ?></td>
<td><?php echo $quest_data['AppName']; ?></td>
<td><a href="javascript:;" onclick="questModify('<?php echo $quest_data['QuestId']; ?>','<?php echo $App; ?>');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('<?php echo $quest_data['QuestId']; ?>','<?php echo $App; ?>')">删除</a>
</td>
</tr>
	<?php } } ?>
<?php } } ?>
</table>
</fieldset>

 
</dl>
<?php include Base_Common::tpl('contentFooter'); ?>