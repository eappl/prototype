{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_server').click(function(){
		addServerBox = divBox.showBox('{tpl:$this.sign/}&ac=add&AppId={tpl:$AppId/}&PartnerId={tpl:$PartnerId/}', {title:'添加服务器',width:600,height:900});
	});
});

function serverModify(mid){
	modifyServerBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&&ServerId=' + mid, {title:'修改服务器', width:600, height:900});
}

function promptDelete(p_id, p_name){
	deleteServerBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&ServerId=' + p_id;}});
}
function obj_onchange(AppId, ret)
{
	obj=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "{tpl:$this.sign/}&ac=partner.by.app&AppId="+AppId+"&PartnerId="+$("#PartnerId").val(),
		
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
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="AppId" id="AppId" onchange="obj_onchange(this.value,'PartnerId')">
  <option value="">--全部--</option>
  {tpl:loop $appArr $v}
  <option value="{tpl:$v.AppId/}" {tpl:if($v.AppId==$AppId)}selected="selected"{/tpl:if}>{tpl:$v.name/}</option>
  {/tpl:loop}
</select>
<select name="PartnerId" id="PartnerId">
<option value="">--全部--</option>
{tpl:loop $rows $v}
 <option value="{tpl:$v.PartnerId/}" {tpl:if($v.PartnerId==$PartnerId)}selected="selected"{/tpl:if}>{tpl:$v.name/}</option>
{/tpl:loop}
</select>
<input type="submit" name="button" id="button" value="查询" />
<input name="all" type="hidden" id="all" value="{tpl:$app/}" />

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

{tpl:loop $serverArr $server}
<tr class="hover">
  <td>{tpl:$server.ServerId/}</td>
  <td>{tpl:$server.name/}</td>
  <td>{tpl:$server.partner_name/}</td>
  <td>{tpl:$server.app_name/}</td>
  <td>{tpl:$server.LoginStart/}</td>
  <td>{tpl:$server.NextEnd/}</td>
  <td>{tpl:$server.NextStart/}</td>
  <td>{tpl:$server.PayStart/}</td>
  <td>{tpl:$server.PayEnd/}</td>
  <td>{tpl:$server.ServerIp/}</td>
  <td>{tpl:$server.SocketPort/}</td>
  <td>{tpl:$server.ServerSocketPort/}</td>
  <td>{tpl:$server.GMIp/}</td>
  <td>{tpl:$server.GMSocketPort/}</td>
  <td><a href="javascript:;" onclick="serverModify('{tpl:$server.ServerId/}')">修改</a>
    | <a href="javascript:;" onclick="promptDelete('{tpl:$server.ServerId/}','{tpl:$server.name/}')">删除</a></td>
</tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}
