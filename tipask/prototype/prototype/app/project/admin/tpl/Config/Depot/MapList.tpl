{tpl:tpl contentHeader/}
<script type="text/javascript">
function machineInfo(mid){
	modifyPartnerBox = divBox.showBox('?ctl=config/machine&ac=get.machine.info&MachineId=' + mid, {title:'机器详情', width:450, height:660});	
}
</script>
<style>
#divdl dl{ float:left; width:150px;border:1px solid #ccc; margin-left:10px;}
#divdl dl dt{ padding-left:5px;}
#divdl dl dd{ height:15px;width:150px; line-height:10px; margin-left:0}
#divdl dl dd img{ width:150px;}
.machine{background-color:#fc9 }
.b10{margin-bottom:10px;}
.pointer{ cursor:pointer;}
#form { margin-bottom:20px;}
</style>
<fieldset><legend>操作</legend>
{tpl:$export_var/}
</fieldset>
<fieldset><legend>机器分布图</legend>
<form action="{tpl:$this.sign/}&ac=machine.map" name="form" id="form" method="post">
			机房：
			<select name = "DepotId" id = "Depot" onchange="getDepotX()">
			{tpl:loop $DepotList $key $depot}
			<option value ="{tpl:$key/}" {tpl:if ($key==$DepotParame.DepotId)}selected{/tpl:if}>{tpl:$depot.name/}</option>
			{/tpl:loop}
			</select>
			机房排数编号：
			<select name = "X" id = "X">
			{tpl:loop $DepotXList $key $val}
			<option value ="{tpl:$val/}"  {tpl:if ($val== $DepotX)}selected{/tpl:if}>{tpl:$val/}</option>
			{/tpl:loop}
			</select>			
<input type="submit" name="Submit" value="查询" />
</form>
	<div class="table table-bordered table-striped" id="divdl">
	<?php if(count($CageList)==0){echo "<div style='margin-left:500px;'>No data to display</div>";} ?>
	{tpl:loop $CageList $CageId $CageInfo}
	<dl>
		<dt>编号：{tpl:$CageInfo.CageCode/}</dt>
		<dt>电量：{tpl:$CageInfo.Current/}A</dt>
		<dt class='b10'>实际电量：{tpl:$CageInfo.ActualCurrent/}A</dt>
		<?php 
		foreach($CageInfo['SizeList'] as $k=> $v)
		{
			if($v == 0)
			{
				echo "<dd>";			
		  }else{
		  	if($v['Flag']==1)//服务器
		  	{
		  		echo "<dd class='pointer' style='height:".($v['Size']*15)."px' ><img onclick=\"machineInfo('".$v['MachineId']."')\"  title='内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."' src='./img/machine/server/server".$v['Size'].".png' style='height:".($v['Size']*15)."px' /> ";		  	
		  	}elseif($v['Flag']==2)//交换机
		  	{
		  		echo "<dd class='pointer' style='height:".($v['Size']*15)."px' ><img onclick=\"machineInfo('".$v['MachineId']."')\" title='内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."' src='./img/machine/exchange/exchange".$v['Size'].".png' style='height:".($v['Size']*15)."px' /> ";			  	
		  	}elseif($v['Flag']==3)//防火墙
		  	{
		  		echo "<dd class='pointer' style='height:".($v['Size']*15)."px' ><img onclick=\"machineInfo('".$v['MachineId']."')\" title='内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."' src='./img/machine/router/router".$v['Size'].".png' style='height:".($v['Size']*15)."px' /> ";			  	
		  	}elseif($v['Flag']==4)//路由器
		  	{
		  		echo "<dd class='pointer' style='height:".($v['Size']*15)."px' ><img onclick=\"machineInfo('".$v['MachineId']."')\" title='内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."' src='./img/machine/router/router".$v['Size'].".png' style='height:".($v['Size']*15)."px' /> ";			  	
		  	}elseif($v['Flag']==5)//其他设备
		  	{
		  		echo "<dd class='pointer' style='height:".($v['Size']*15)."px' ><img onclick=\"machineInfo('".$v['MachineId']."')\" title='内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."' src='./img/machine/other/other".$v['Size'].".png' style='height:".($v['Size']*15)."px' /> ";			  	
		  	}
		  }
			echo "</dd>";
		}
		
		?>
		 
	</dl>
	{/tpl:loop}
  </div>
</fieldset>


<script type="text/javascript">
$(function(){

	//getDepotX();

})
function getDepotX()
{
	var DepotId=$("#Depot").val();
	
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/cage&ac=get.depot.x"+"&DepotId="+DepotId,	
		success: function(msg)
		{
			$("#X").html(msg);
				
		}
	});
}
 </script>
 {tpl:tpl contentFooter/}
