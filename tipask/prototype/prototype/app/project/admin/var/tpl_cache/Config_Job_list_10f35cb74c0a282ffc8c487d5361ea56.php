<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_job').click(function(){
		addJobBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add', {title:'添加职业',width:500,height:300});
	});
});
function jobModify(m_id,p_id){
	modifyJobBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&JobId=' + m_id + '&AppId=' + p_id, {title:'修改职业', width:500, height:300});
}

function promptDelete(m_id,p_id){
	deleteProductBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&AppId=' + p_id + '&JobId=' + m_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_job">添加职业</a> ]
</fieldset>
<fieldset><legend>职业列表</legend>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
			选择游戏
			<select name = "AppId" id = "AppId">
			<?php if (is_array($AppList)) { foreach ($AppList as $key => $app) { ?>
			<option value = <?php echo $key; ?> <?php if($key==$AppId) { ?>selected<?php } ?>><?php echo $app['name']; ?></option>
			<?php } } ?>
			</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">
<tr><th align="center" class="rowtip">职业ID</th>
<th align="center" class="rowtip">职业名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th></tr>
<?php if (is_array($JobArr)) { foreach ($JobArr as $App => $app_data) { ?>
	<?php if (is_array($app_data)) { foreach ($app_data as $Job => $job_data) { ?>
<tr>
<td><?php echo $job_data['JobId']; ?></td>
<td><?php echo $job_data['name']; ?></td>
<td><?php echo $job_data['AppName']; ?></td>
<td><a href="javascript:;" onclick="jobModify('<?php echo $job_data['JobId']; ?>','<?php echo $App; ?>');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('<?php echo $job_data['JobId']; ?>','<?php echo $App; ?>')">删除</a>
</td>
</tr>
	<?php } } ?>
<?php } } ?>
</table>
</fieldset>

 
</dl>
<?php include Base_Common::tpl('contentFooter'); ?>
