<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_server').click(function(){
		addServerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add&AppId=<?php echo $AppId; ?>&PartnerId=<?php echo $PartnerId; ?>', {title:'添加服务器',width:600,height:900});
	});
});

function serverModify(mid){
	modifyServerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&&ServerId=' + mid, {title:'修改服务器', width:600, height:900});
}

function promptDelete(p_id, p_name){
	deleteServerBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&ServerId=' + p_id;}});
}
function obj_onchange(AppId, ret)
{
	obj=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "<?php echo $this->sign; ?>&ac=partner.by.app&AppId="+AppId+"&PartnerId="+$("#PartnerId").val(),
		
		success: function(msg)
		{
			$("#"+ret).html(msg);
		}
	});
	//*/
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_server">添加服务器</a> ]

</fieldset>

<fieldset><legend>服务器列表 </legend>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
<select name="AppId" id="AppId" onchange="obj_onchange(this.value,'PartnerId')">
  <option value="">--全部--</option>
  <?php if (is_array($appArr)) { foreach ($appArr as $v) { ?>
  <option value="<?php echo $v['AppId']; ?>" <?php if($v['AppId']==$AppId) { ?>selected="selected"<?php } ?>><?php echo $v['name']; ?></option>
  <?php } } ?>
</select>
<select name="PartnerId" id="PartnerId">
<option value="">--全部--</option>
<?php if (is_array($rows)) { foreach ($rows as $v) { ?>
 <option value="<?php echo $v['PartnerId']; ?>" <?php if($v['PartnerId']==$PartnerId) { ?>selected="selected"<?php } ?>><?php echo $v['name']; ?></option>
<?php } } ?>
</select>
<input type="submit" name="button" id="button" value="查询" />
<input name="all" type="hidden" id="all" value="<?php echo $app; ?>" />

<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
  <th align="center" class="rowtip">服务器Id</th>
  <th align="center" class="rowtip">名称</th>
  <th align="center" class="rowtip">平台</th>
  <th align="center" class="rowtip">游戏</th>
  <th align="center" class="rowtip">开服时间</th>
  <th align="center" class="rowtip">开始停服时间</th>
  <th align="center" class="rowtip">再次开服时间</th>
  <th align="center" class="rowtip">开始充值时间</th>
  <th align="center" class="rowtip">结束充值时间</th>
  <th align="center" class="rowtip">服务器IP</th>
  <th align="center" class="rowtip">Socket端口</th>
  <th align="center" class="rowtip">服务器Socket端口</th>
  <th align="center" class="rowtip">GM服务器IP</th>
  <th align="center" class="rowtip">GM服务器Socket端口</th>
  <th align="center" class="rowtip">操作</th>
  </tr>

<?php if (is_array($serverArr)) { foreach ($serverArr as $server) { ?>
<tr class="hover">
  <td><?php echo $server['ServerId']; ?></td>
  <td><?php echo $server['name']; ?></td>
  <td><?php echo $server['partner_name']; ?></td>
  <td><?php echo $server['app_name']; ?></td>
  <td><?php echo $server['LoginStart']; ?></td>
  <td><?php echo $server['NextEnd']; ?></td>
  <td><?php echo $server['NextStart']; ?></td>
  <td><?php echo $server['PayStart']; ?></td>
  <td><?php echo $server['PayEnd']; ?></td>
  <td><?php echo $server['ServerIp']; ?></td>
  <td><?php echo $server['SocketPort']; ?></td>
  <td><?php echo $server['ServerSocketPort']; ?></td>
  <td><?php echo $server['GMIp']; ?></td>
  <td><?php echo $server['GMSocketPort']; ?></td>
  <td><a href="javascript:;" onclick="serverModify('<?php echo $server['ServerId']; ?>')">修改</a>
    | <a href="javascript:;" onclick="promptDelete('<?php echo $server['ServerId']; ?>','<?php echo $server['name']; ?>')">删除</a></td>
</tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
