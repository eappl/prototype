{tpl:tpl contentHeader/}
<style>
.table-striped tbody tr:nth-child(2n+1) td, .table-striped tbody tr:nth-child(2n+1) th {
    background-color: #CCCCCC;
}
</style>
<div class="br_bottom"></div>
<form name="machine_modify_form" id="machine_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table width="99%" align="center" class="table table-bordered table-striped">
<tr>
<td>机器编码</td>
<td colspan="3">{tpl:$MachineInfo.MachineCode/} </td>
</tr>

<tr>
			<td>机房</td>
			<td>{tpl:$MachineInfo.DepotName/}</td>
			<td>机柜</td>	
			<td>{tpl:$MachineInfo.CageCode/}</td>				
</tr>

<tr>
			<td>机柜位置</td>
			<td>{tpl:$MachineInfo.Position/}</td>
		  <td>尺寸</td>
			<td>{tpl:$MachineInfo.Size/}</td>		
</tr>

<tr>
<td>额定电流</td>
<td colspan="3">{tpl:$MachineInfo.Current/}A</td>
</tr>

<tr>
<td>内网ip</td>
<td>{tpl:$MachineInfo.LocalIP/}</td>
<td>公网ip</td>
<td>{tpl:$MachineInfo.WebIP/}</td>
</tr>

<tr>
			<td>游戏:</td>
			<td id="ServerTd">{tpl:$MachineInfo.AppName/}</td>
			<td class="PartnerTd">平台:</td>
			<td class="PartnerTd">
				{tpl:$MachineInfo.PartnerName/}
			</td>		
</tr>
		
<tr>
<td>服务器:</td>
<td colspan="3">{tpl:$MachineInfo.ServerName/}</td>
</tr>

<tr>
<td>资产编号</td>
<td>{tpl:$MachineInfo.EstateCode/}</td>
<td>实物状态</td>
<td>{tpl:$MachineInfo.MachineStatus/}</td>
</tr>

<tr>
<td>固定资产</td>
<td>{tpl:$MachineInfo.MachineName/}</td>
<td>资产型号</td>
<td>{tpl:$MachineInfo.Version/}</td>
</tr>

<tr>
	<td>实物标签</td>
	<td>{tpl:$MachineInfo.Comment.Status/}</td>
	<td>知识产权</td>
	<td>{tpl:$MachineInfo.IntellectProperty/}</td>
</tr>

<tr>
<td>使用人</td>
<td>{tpl:$MachineInfo.User/}</td>
<td></td>
<td></td>
</tr>

<tr>
<td>CPU</td>
<td>{tpl:$MachineInfo.Comment.Cpu/}</td>
<td>CPU数量</td>
<td>{tpl:$MachineInfo.Comment.CpuCount/}</td>
</tr>

<tr>
<td>内存</td>
<td>{tpl:$MachineInfo.Comment.Memory/}</td>
<td>内存数量</td>
<td>{tpl:$MachineInfo.Comment.MemoryCount/}</td>
</tr>

<tr>
<td>硬盘</td>
<td>{tpl:$MachineInfo.Comment.Hd/}</td>
<td>硬盘数量</td>
<td>{tpl:$MachineInfo.Comment.HdCount/}</td>
</tr>

<tr>
<td>硬盘模式</td>
<td colspan="3">{tpl:$MachineInfo.Comment.HdMode/}</td>
</tr>

<tr>
<td>网卡</td>
<td>{tpl:$MachineInfo.Comment.Netcard/}</td>
<td>网卡数量</td>
<td>{tpl:$MachineInfo.Comment.NetcardCount/}</td>
</tr>

<tr>
<td>用途</td>
<td colspan="3">{tpl:$MachineInfo.Purpose/}</td>
</tr>
<tr>
<td>说明</td>
<td colspan="3">{tpl:$MachineInfo.Comment.Remark/}</td>
</tr>
</table>
</form>
<script type="text/javascript">
$(function(){
	var tex = $("#ServerTd").text();
	if(tex=='其它')
	{
		$(".PartnerTd").hide();
	}
	
})
</script>
{tpl:tpl contentFooter/}