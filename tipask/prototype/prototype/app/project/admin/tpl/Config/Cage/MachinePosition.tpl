{tpl:tpl contentHeader/}
<style>
.machine{background: #FFCC99;}
</style>
<script type="text/javascript">
function machineInfo(mid){
	modifyPartnerBox = divBox.showBox('?ctl=config/machine&ac=get.machine.info&MachineCode=' + mid, {title:'机器位置图', contentType:'ajax', width:450, height:500, showOk:false, showCancel:false});	
}
</script>
<table class="table table-bordered table-striped">
<?php
		$i = 1;
		foreach($CageMap as $row => $row_info)
		{
			if($row_info==0)
			{
				echo "<tr><td width='10%'>$i</td><td>empty</td></tr>";				
			}
			elseif(is_array($row_info))
			{
	
				for($j=$i;$j<=$row_info['Size']+$i-1;$j++)
				{
					if($j==$i)
					{
						 echo "<tr > <td width='10%'>$j</td><td class='machine' rowspan = ".$row_info['Size']."><a href='javascript:;' onclick=\"machineInfo('".$row_info['MachineCode']."');\">内网IP:".$row_info['LocalIP']." <br/> 公网IP:".$row_info['WebIP']." <br/> 说明：".$row_info['Comment']['Remark']."</a></td></tr>";
					}
					else
					{
						 echo "<tr rowspan = 1><td>$j</td></tr>";
					}
					
				}
				$i+=$row_info['Size']-1;
			}
			$i++;	
		}?>
</table>
{tpl:tpl contentFooter/}
