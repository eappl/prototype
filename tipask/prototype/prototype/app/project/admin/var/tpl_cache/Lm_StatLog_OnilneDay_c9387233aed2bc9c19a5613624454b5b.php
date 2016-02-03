<?php include Base_Common::tpl('contentHeader'); ?>
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
<form name="selection" id="selection" action="<?php echo $page_form_action; ?>" method="post">
<dl><dt><?php echo $page_title; ?></dt>
<dd>
<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
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
日期
<input type="text" name="Date" value="<?php echo $Date; ?>" class="input-small" size = 12 onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />

<input type = 'submit' class="btn btn-info btn-small" value = '查询'><?php echo $export_var; ?>
</form>
    <?php
      $FC->renderChart();
    ?>

<table class="table table-bordered table-striped">
<tr><th rowspan = 2 style="text-align:center">时间</th><th colspan = 2 style="text-align:center">在线统计</th></tr>
<tr><th style="text-align:center">最低在线</th><th style="text-align:center">平均在线</th></tr>
<?php if (is_array($OnlineDayArr)) { foreach ($OnlineDayArr as $time => $UserOnline) { ?>
<tr>
<td style="text-align:center"><?php echo date('H:i',$time); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$UserOnline['LowOnline']),0); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$UserOnline['AvgOnline']),0); ?></td>
</tr>
<?php } } ?>
</table>
</dd>
</dl>
<?php include Base_Common::tpl('contentFooter'); ?>
