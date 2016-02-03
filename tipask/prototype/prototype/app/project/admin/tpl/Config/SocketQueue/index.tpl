{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_queue').click(function(){
		addQueueBox = divBox.showBox('{tpl:$this.sign/}&ac=add.queue', {title:'添加发送队列', contentType:'ajax', width:600, height:630});
	});
});
function obj_onchange_partner()
{
	app=$("#AppId");
	partner=$("#PartnerId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/server&ac=server.by.app.partner&AppId="+$("#AppId").val()+"&PartnerId="+$("#PartnerId").val(),
		
		success: function(msg)
		{
			$("#ServerId").html(msg);
		}
	});
	//*/
}function obj_onchange_app()
{
	app=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/server&ac=partner.by.app&AppId="+$("#AppId").val(),
		
		success: function(msg)
		{
			$("#PartnerId").html(msg);
		}
	});
	//*/
}
function promptDelete(m_id,MessegeContent){						
	deleteDepotBox = divBox.confirmBox({content:'是否删除 ' + MessegeContent + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&queueId=' + m_id;}});	
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_queue">添加队列</a> ]
</fieldset>

<fieldset><legend>队列列表</legend>

<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
  <th align="center" class="rowtip">队列ID</th>
  <th align="center" class="rowtip">服务器ID</th>
  <th align="center" class="rowtip">操作类型</th>
  <th align="center" class="rowtip">预计发送时间</th>
  <th align="center" class="rowtip">内容</th>
  <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $CurrentQueue $queueId $queueInfo}
<tr class="hover">
  <td>{tpl:$queueId/}</td>
  <td>{tpl:$queueInfo.ServerId/}</td>
  <td>{tpl:$queueInfo.uTypeName/}</td>
  <td>{tpl:$queueInfo.QueueTime/}</td>
  <td>{tpl:if($uType==$queueInfo.uType)}{tpl:$queueInfo.MessegeContent/}{/tpl:if}</td>
   {tpl:if($uType==$queueInfo.uType)}
   <td><a href="javascript:;" onclick="promptDelete('{tpl:$queueId/}','内容为 {tpl:$queueInfo.MessegeContent/} 发送时间:{tpl:$queueInfo.QueueTime/}这条队列')">删除</a></td>
   {tpl:else}
   <td></td>
   {/tpl:if}   
</tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}