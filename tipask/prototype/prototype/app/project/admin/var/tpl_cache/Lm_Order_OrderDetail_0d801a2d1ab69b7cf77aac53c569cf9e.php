<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
function createExchange(orderid){
	createExchangeBox = divBox.showBox('<?php echo $sign; ?>&ac=create.exchange.by.manager&OrderId=' + orderid, {title:'订单详情', contentType:'ajax', width:500, height:500});
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

</script>

<dd><ul class="clearfix">
</ul></dd></dl>
<form name="selection" id="selection" action="<?php echo $page_form_action; ?>" method="post">
<dl><dt><?php echo $page_title; ?></dt>
<dd>
<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
用户名：
<input type = 'text' name = "UserName" id = "UserName" value = <?php echo $UserName; ?>>
订单号：
<input type = 'text' name = "OrderId" id = "OrderId" value = <?php echo $OrderId; ?>>
选择游戏:
<select name = "AppId" id = "AppId" onchange="getpermittedweeparter()">
	<option value = 0 <?php if(0==$AppId) { ?>selected<?php } ?>> 全部 </option>
	<?php if (is_array($permitted_app)) { foreach ($permitted_app as $key => $app) { ?>
<option value = <?php echo $key; ?> <?php if($key==$AppId) { ?>selected<?php } ?>><?php echo $app['name']; ?></option>
<?php } } ?>
</select>
选择平台:
<select name = "PartnerId" id = "PartnerId" onchange="getpermittedserver()">
	<option value = 0 <?php if(0==$PartnerId) { ?>selected<?php } ?>> 全部 </option>
	 <?php if (is_array($permitted_partner)) { foreach ($permitted_partner as $partner_key => $partner) { ?>
			<option value = <?php echo $partner_key; ?> <?php if($partner_key==$PartnerId) { ?>selected<?php } ?>><?php echo $partner['name']; ?></option>
	 <?php } } ?>
</select>
选择服务器:
<select name = "ServerId" id = "ServerId">
	<option value = 0 <?php if(0==$ServerId) { ?>selected<?php } ?>> 全部 </option>
	<?php if (is_array($permitted_server)) { foreach ($permitted_server as $server_key => $server) { ?>
		<option value = <?php echo $server_key; ?> <?php if($server_key==$ServerId) { ?>selected<?php } ?>><?php echo $server['name']; ?></option>
	<?php } } ?>
</select>
订单状态:
<select name = "OrderStatus" id = "OrderStatus">
	<?php if (is_array($OrderStatusList)) { foreach ($OrderStatusList as $status => $statusname) { ?>
	<option value = <?php echo $status; ?> <?php if($status==$OrderStatus) { ?>selected<?php } ?>> <?php echo $statusname; ?> </option>
		<?php } } ?>
</select>
时间段
<input type="text" name="StartTime" value="<?php echo $StartTime; ?>" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" />
到
<input type="text" name="EndTime" value="<?php echo $EndTime; ?>" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" />
<input type = 'submit' class="btn btn-info btn-small" value = '查询'><?php echo $export_var; ?>
</form>

<table class="table table-bordered table-striped">
<tr><th style="text-align:center">订单号</th><th style="text-align:center">支付方用户</th><th style="text-align:center">接收方用户</th><th style="text-align:center">游戏</th><th style="text-align:center">平台</th><th style="text-align:center">大区</th><th style="text-align:center">支付方式</th><th style="text-align:center">付费金额</th><th style="text-align:center">平台货币金额</th><th style="text-align:center">应得积分</th><th style="text-align:center">下单时间</th><th style="text-align:center">下单IP</th><th style="text-align:center">支付时间</th><th style="text-align:center">支付IP</th><th style="text-align:center">订单状态</th><th style="text-align:center">操作</th></tr>

<?php if (is_array($OrderDetailArr['OrderDetail'])) { foreach ($OrderDetailArr['OrderDetail'] as $OrderId => $OrderInfo) { ?>
<tr>
<td style="text-align:center"><?php echo $OrderId; ?></td>
<td style="text-align:center"><?php echo $OrderInfo['PayUserName']; ?></td>
<td style="text-align:center"><?php echo $OrderInfo['AcceptUserName']; ?></td>
<td style="text-align:center"><?php echo $OrderInfo['AppName']; ?></td>
<td style="text-align:center"><?php echo $OrderInfo['PartnerName']; ?></td>
<td style="text-align:center"><?php echo $OrderInfo['ServerName']; ?></td>
<td style="text-align:center"><?php echo $OrderInfo['PassageName']; ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$OrderInfo['Amount']),0); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$OrderInfo['Coin']),0); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$OrderInfo['Credit']),0); ?></td>
<td style="text-align:center"><?php echo date('Y-m-d H:i:s',$OrderInfo['OrderTime']); ?></td>
<td style="text-align:center"><?php echo long2ip($OrderInfo['OrderIP']); ?></td>
<td style="text-align:center"><?php echo date('Y-m-d H:i:s',$OrderInfo['PayTime']); ?></td>
<td style="text-align:center"><?php echo long2ip($OrderInfo['PayIP']); ?></td>
<td style="text-align:center"><?php echo $OrderInfo['OrderStatusName']; ?></td>
<td style="text-align:center"><?php if($OrderInfo['CreateExchange']==1) { ?><a href="javascript:;" onclick="createExchange('<?php echo $OrderInfo['OrderId']; ?>');">补单</a><?php } ?></td>

</tr>
<?php } } ?>
</table>
<?php echo $page_content; ?>

</dd>
</dl>
<?php include Base_Common::tpl('contentFooter'); ?>
