<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript" src="lib/jquery-ui/jquery-ui-1.8.23.custom.min.js"></script>
<link rel="stylesheet" href="lib/jquery-ui/css/Aristo/Aristo.css"  /> 
   
<script type="text/javascript">
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
<select name = "PartnerId" id = "PartnerId" >
	<option value = 0 <?php if(0==$PartnerId) { ?>selected<?php } ?>> 全部 </option>
	 <?php if (is_array($permitted_partner)) { foreach ($permitted_partner as $partner_key => $partner) { ?>
			<option value = <?php echo $partner_key; ?> <?php if($partner_key==$PartnerId) { ?>selected<?php } ?>><?php echo $partner['name']; ?></option>
	 <?php } } ?>
</select>

选择支付方式:
<select name = "PassageId" id = "PassageId">
	<option value = 0 <?php if(0==$PassageId) { ?>selected<?php } ?>> 全部 </option>
	 <?php if (is_array($PassageList)) { foreach ($PassageList as $passage_key => $passage) { ?>
			<option value = <?php echo $passage_key; ?> <?php if($passage['passage_id']==$PassageId) { ?>selected<?php } ?>><?php echo $passage['name']; ?></option>
	 <?php } } ?>
</select>

时间段
<input type="text" name="StartDate" value="<?php echo $StartDate; ?>" class="input-small" size = 12 onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />
到
<input type="text" name="EndDate" value="<?php echo $EndDate; ?>" class="input-small" size = 12 onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />
<input type = 'submit' class="btn btn-info btn-small" value = '查询'><?php echo $export_var; ?>
</form>
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">日期</a></li>
		<li><a href="#tabs-2">标签2</a></li>
	</ul>
	<div id="tabs-1">
    <?php
      $FC->renderChart();
      $FC2->renderChart();

    ?>
<table class="table table-bordered table-striped">
<tr><th style="text-align:center">日期</th><th style="text-align:center">付款金额</th><th style="text-align:center">付款次数</th><th style="text-align:center">付款人数</th><th style="text-align:center">人均付款</th><th style="text-align:center">单次付款</th></tr>
<tr>
	<td style="text-align:center">总计:</td>
<td style="text-align:center"><?php echo sprintf('%10.4f',$PayDayArr['TotalData']['ConvertedAmount']); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$PayDayArr['TotalData']['PayCount']),0); ?></td>
<td style="text-align:center"></td>
<td style="text-align:center"></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$PayDayArr['TotalData']['AmountPerPay']),0); ?></td>

</tr>
<?php if (is_array($PayDayArr['PayDate'])) { foreach ($PayDayArr['PayDate'] as $Date => $DateInfo) { ?>
<tr>
<td style="text-align:center"><?php echo $Date; ?></td>
<td style="text-align:center"><?php echo sprintf('%10.4f',$DateInfo['Total']['ConvertedAmount']); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$DateInfo['Total']['PayCount']),0); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$DateInfo['Total']['PayUser']),0); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$DateInfo['Total']['AmountPerUser']),0); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$DateInfo['Total']['AmountPerPay']),0); ?></td>

</tr>
<?php } } ?>
	<td style="text-align:center">总计:</td>
<td style="text-align:center"><?php echo sprintf('%10.4f',$PayDayArr['TotalData']['ConvertedAmount']); ?></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$PayDayArr['TotalData']['PayCount']),0); ?></td>
<td style="text-align:center"></td>
<td style="text-align:center"></td>
<td style="text-align:center"><?php echo number_format(sprintf('%10d',$PayDayArr['TotalData']['AmountPerPay']),0); ?></td>
</table>	
	
	</div>
	<div id="tabs-2">暂无数据</div>
</div>


</dd>
</dl>
<script>$("#tabs").tabs();</script>
<?php include Base_Common::tpl('contentFooter'); ?>