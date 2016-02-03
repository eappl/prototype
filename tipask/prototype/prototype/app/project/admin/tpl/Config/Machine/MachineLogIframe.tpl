{tpl:tpl contentHeader/}
<style>
#chileTable th{align:center;}
#chileTable th,#chileTable td{white-space:nowrap;}
</style>
<table  class="table table-bordered table-striped" width="100%" id="chileTable">
<tr>
<th>类型</th>
<th>用户</th>
<th>日期</th>
<th>类型</th>
<th>序列号</th>
<th>资产编号</th>
<th>固定资产</th>
<th>资产型号</th>
<th>机房</th>
<th>机柜</th>

<th>游戏</th>
<th>平台</th>
<th>服务器</th>

<th>位置</th>
<th>机器U高</th>
<th>额定电流</th>

<th>内网IP</th>
<th>公网IP</th>

<th>实物状态</th>
<th>实物标签</th>
<th>知识产权</th>

<th>使用人</th>
<th>金额</th>

<th>CPU</th>
<th>CPU数量</th>
<th>内存</th>
<th>内存数量</th>
<th>硬盘</th>
<th>硬盘数量</th>
<th>硬盘模式</th>
<th>网卡</th>
<th>网卡数量</th>

<th>用途</th>
<th>备注</th>
</tr>
{tpl:loop $MachineLogArr $key $machine_data}
<tr>
<td><span style="color:red;">{tpl:$machine_data.Tip/}</span></td>
<td>{tpl:$machine_data.Name/}</td>
<td>{tpl:$machine_data.LogDate/}</td>
<td>{tpl:$machine_data.Flag/}</td>
<td>{tpl:$machine_data.MachineCode/}</td>
<td>{tpl:$machine_data.EstateCode/}</td>
<td>{tpl:$machine_data.MachineName/}</td>
<td>{tpl:$machine_data.Version/}</td>
<td>{tpl:$machine_data.DepotName/}</td>
<td>{tpl:$machine_data.CageCode/}</td>

<td>{tpl:$machine_data.AppName/}</td>
<td>{tpl:$machine_data.PartnerName/}</td>
<td>{tpl:$machine_data.ServerName/}</td>

<td>{tpl:$machine_data.Position/}</td>
<td>{tpl:$machine_data.Size/}</td>
<td>{tpl:$machine_data.Current/}A</td>

<td>{tpl:$machine_data.LocalIP/}</td>
<td>{tpl:$machine_data.WebIP/}</td>

<td>{tpl:$machine_data.MachineStatus/}</td>
<td>{tpl:$machine_data.Comment.Status/}</td>
<td>{tpl:$machine_data.IntellectProperty/}</td>


<td>{tpl:$machine_data.User/}</td>
<td>{tpl:$machine_data.Comment.Money/}</td>

<td>{tpl:$machine_data.Comment.Cpu/}</td>
<td>{tpl:$machine_data.Comment.CpuCount/}</td>
<td>{tpl:$machine_data.Comment.Memory/}</td>
<td>{tpl:$machine_data.Comment.MemoryCount/}</td>
<td>{tpl:$machine_data.Comment.Hd/}</td>
<td>{tpl:$machine_data.Comment.HdCount/}</td>
<td>{tpl:$machine_data.Comment.HdMode/}</td>
<td>{tpl:$machine_data.Comment.Netcard/}</td>
<td>{tpl:$machine_data.Comment.NetcardCount/}</td>

<td>{tpl:$machine_data.Purpose/}</td>
<td>{tpl:$machine_data.Comment.Remark/}</td>

</tr>
{/tpl:loop}
</table>


<script type="text/javascript">
$(function(){
	//table th的样式
	$("#chileTable th").addClass("rowtip");
})
</script>
{tpl:tpl contentFooter/}