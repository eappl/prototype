{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_testuser').click(function(){
		addPartnerBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加测试用户', contentType:'ajax', width:300, height:300, showOk:false, showCancel:false});
	});
});

function promptDelete(username,app,partner){
	deleteTestUserBox = divBox.showBox('是否删除?', {title:'删除确认!',onok:function(){document.location = '{tpl:$this.sign/}&ac=delete&username=' + username+'&AppId=' + app +'&PartnerId=' + partner;}});
}
function getpermittedserver()
{
	AppId=$("#AppId");
	partner=$("#PartnerId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.server&AppId="+AppId.val()+"&PartnerId="+partner.val(),
		
		success: function(msg)
		{
			$("#ServerId").html(msg);
		}
	});
	//*/
}
function getpermittedweeapp()
{
	app_type=$("#app_type");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.app&app_type="+app_type.val(),
		
		success: function(msg)
		{
			$("#AppId").html(msg);
		}
	});
	//*/
}
function getpermittedweeparter()
{
	partner_type=$("#partner_type");
	AppId=$("#AppId");
	is_abroad=$("#is_abroad");
	AreaId=$("#AreaId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.partner&AppId="+AppId.val()+"&partner_type="+partner_type.val()+"&is_abroad="+is_abroad.val()+"&AreaId="+AreaId.val(),
		success: function(msg)
		{
			$("#PartnerId").html(msg);
		}
	});
	//*/
}
function getarea()
{
	is_abroad=$("#is_abroad");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/area&ac=get.area&is_abroad="+is_abroad.val(),
		
		success: function(msg)
		{
			$("#AreaId").html(msg);
		}
	});
	//*/
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_testuser">添加测试用户</a> ]
</fieldset>
<fieldset><legend>测试用户列表</legend>
<table class="table table-bordered table-striped">
<form name="selection" id="selection" action="{tpl:$page_form_action /}" method="post">
<input type = 'hidden' name = "app_type" id = "app_type" value = {tpl:$app_type/}>
<input type = 'hidden' name = "AreaId" id = "AreaId" value = {tpl:$AreaId/}>
<input type = 'hidden' name = "partner_type" id = "partner_type" value = {tpl:partner_type/}>
<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = {tpl:$is_abroad/}>


选择游戏:
<select name = "AppId" id = "AppId" onchange="getpermittedweeparter()">
	<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
	{tpl:loop $permitted_app $key $app}
<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
{/tpl:loop}
</select>
选择平台:
<select name = "PartnerId" id = "PartnerId" onchange="getpermittedserver()">
	<option value = 0 {tpl:if (0==$PartnerId)}selected{/tpl:if}> 全部 </option>
	 {tpl:loop $permitted_partner $partner_key $partner}
			<option value = {tpl:$partner_key/} {tpl:if ($partner_key==$PartnerId)}selected{/tpl:if}>{tpl:$partner.name/}</option>
	 {/tpl:loop}
</select>
<input type = 'submit' class="btn btn-info btn-small" value = '查询'>
<tr><th align="center" class="rowtip">用户名</th>
<th align="center" class="rowtip">游戏</th>
<th align="center" class="rowtip">合作商</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $totalUser $key $userInfo}
<tr>
<td>{tpl:$userInfo.username/}</td>
<td>{tpl:$userInfo.app_name/}</td>
<td>{tpl:$userInfo.partner_name/}</td>
<td><a  href="javascript:;" onclick="promptDelete('{tpl:$userInfo.username func="urlencode(@@)"/}','{tpl:$userInfo.AppId/}','{tpl:$userInfo.PartnerId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
{tpl:tpl contentFooter/}
