
{tpl:tpl contentHeader/}
<script type="text/javascript">
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
</script>

<dd><ul class="clearfix">
</ul></dd></dl>
<form name="selection" id="selection" action="{tpl:$page_form_action /}" method="post">
<dl><dt>{tpl:$page_title /}</dt>
<dd>
<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
用户名：
<input type = 'text' name = "UserName" id = "UserName" value = {tpl:$UserName/}>
发送类型:
<select name = "ProductSendType" id = "ProductSendType">
	{tpl:loop $ProductSendTypeArr $sendtype $sendtypename}
	<option value = {tpl:$sendtype/} {tpl:if ($sendtype==$ProductSendType)}selected{/tpl:if}> {tpl:$sendtypename/} </option>
		{/tpl:loop}
</select>
产品类型:
<select name = "ProductType" id = "ProductType">
	{tpl:loop $ProductTypeArr $type $typename}
	<option value = {tpl:$type/} {tpl:if ($type==$ProductType)}selected{/tpl:if}>{tpl:$typename/} </option>
		{/tpl:loop}
</select>
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
选择服务器:
<select name = "ServerId" id = "ServerId">
	<option value = 0 {tpl:if (0==$ServerId)}selected{/tpl:if}> 全部 </option>
	{tpl:loop $permitted_server $server_key $server}
		<option value = {tpl:$server_key/} {tpl:if ($server_key==$ServerId)}selected{/tpl:if}>{tpl:$server.name/}</option>
	{/tpl:loop}
</select>


<input type = 'submit' class="btn btn-info btn-small" value = '查询'>
</form>

<table class="table table-bordered table-striped">
<tr><th style="text-align:center">发送类型</th><th style="text-align:center">道具类型</th><th style="text-align:center">发送批次ID</th><th style="text-align:center">用户</th><th style="text-align:center">大区</th><th style="text-align:center">道具ID</th><th style="text-align:center">预计发送时间</th><th style="text-align:center">发送状态</th></tr>

{tpl:loop $ProductSendQueueDetailArr.ProductSendQueueDetail $key $QueueInfo}
<tr>
<td style="text-align:center">{tpl:$QueueInfo.ProductSendTypeName /}</td>
<td style="text-align:center">{tpl:$QueueInfo.ProductTypeName /}</td>
<td style="text-align:center">{tpl:$QueueInfo.SendId/}</td>
<td style="text-align:center">{tpl:$QueueInfo.UserName /}</td>
<td style="text-align:center">{tpl:$QueueInfo.ServerName/}</td>
<td style="text-align:center">{tpl:$QueueInfo.ProductId/}</td>
<td style="text-align:center">{tpl:$QueueInfo.ToSendTime func="date('Y-m-d H:i:s',@@)"/}</td>
<td style="text-align:center">{tpl:if($QueueInfo.SendStatus>0)}已加入发送队列{tpl:else}尚未加入发送队列{/tpl:if}</td>

</tr>
{/tpl:loop}
</table>

</dd>
</dl>
{tpl:tpl contentFooter/}
