{tpl:tpl contentHeader/}
	<table>
	<tr><td>{tpl:$DepotName/}</td><td colspan='2'>{tpl:$DepotX/}</td></tr>
	<?php if(count($CageList)==0){echo "<div style='margin-left:500px;'>No data to display</div>";} ?>
	{tpl:loop $CageList $CageId $CageInfo}
	<tr><td></td><td></td></tr>
		<tr><td>机柜编号：</td><td>{tpl:$CageInfo.CageCode/}</td></tr>
		<tr><td>电量：</td><td>{tpl:$CageInfo.Current/}A</td></tr>
		<tr><td>实际电量：</td><td>{tpl:$CageInfo.ActualCurrent/}A</td></tr>
		<?php 
		foreach($CageInfo['SizeList'] as $k=> $v)
		{
			if($v == 0)
			{
				echo "<tr><td></td><td></td> ";			
		  }else{
		  	if($v['Flag']==1)//服务器
		  	{
		  		echo "<tr rowspan='{$v['Size']}'><td colspan='3'><img onclick=\"machineInfo('".$v['MachineCode']."')\"  src='./img/machine/server/server".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td><td colspan='2'>内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."</td>";		  	
		  	}elseif($v['Flag']==2)//交换机
		  	{
		  		echo "<tr rowspan='{$v['Size']}'><td colspan='3'><img onclick=\"machineInfo('".$v['MachineCode']."')\"  src='./img/machine/exchange/exchange".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td colspan='2'> <td>内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."</td>";			  	
		  	}elseif($v['Flag']==3)//防火墙
		  	{
		  		echo "<tr rowspan='{$v['Size']}'><td colspan='3'><img onclick=\"machineInfo('".$v['MachineCode']."')\" src='./img/machine/router/router".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td><td colspan='2'>内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."</td> ";			  	
		  	}elseif($v['Flag']==4)//路由器
		  	{
		  		echo "<tr rowspan='{$v['Size']}'><td colspan='3'><img onclick=\"machineInfo('".$v['MachineCode']."')\" src='./img/machine/router/router".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td><td colspan='2'>内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."</td> ";			  	
		  	}elseif($v['Flag']==5)//其他设备
		  	{
		  		echo "<tr rowspan='{$v['Size']}'><td colspan='3'><img onclick=\"machineInfo('".$v['MachineCode']."')\" src='./img/machine/other/other".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td><td colspan='2'>内网IP：".$v['LocalIP']." 公网IP：".$v['WebIP']." 用途：".$v['Purpose']."</td>";			  	
		  	}
		  }
			echo "</tr>";
		}
		
		?>
		 	
	{/tpl:loop}
  </table>
 {tpl:tpl contentFooter/}
