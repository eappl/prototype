{tpl:tpl contentHeader/}
<style>
form{ margin:0;}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_machine').click(function(){
		addNetworkBox = divBox.showBox('{tpl:$this.sign/}&ac=network.add', {title:'添加网络设备',width:700, height:500});
	});
});
function machineModify(mid){
	modifyNetworkBox = divBox.showBox('{tpl:$this.sign/}&ac=network.modify&MachineId=' + mid, {title:'修改网络设备', width:700, height:500});
}

function promptDelete(m_id,MachineCode){
	deleteNetworkBox = divBox.confirmBox({content:'是否删除 ' + MachineCode + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&MachineId=' + m_id+"&Flag=8";}});
}
function machineInfo(mid){
	MachineInfoBox = divBox.showBox('{tpl:$this.sign/}&ac=get.machine.info&MachineId=' + mid, {title:'机器详情', width:450, height:500});	
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_machine">添加网络设备</a> ]&nbsp;&nbsp;&nbsp;&nbsp;
{tpl:$export_var/}
</fieldset>
<fieldset><legend>网络设备列表</legend>

<form action="{tpl:$this.sign/}&ac=index" name="form" id="form" method="post">
			序列号：<input type="text" name="MachineCode" id="MachineCode" class="span2" value="{tpl:$param.MachineCode/}"/>
			资产编号：<input type="text" name="EstateCode" id="EstateCode" class="span2" value="{tpl:$param.EstateCode/}"/>
			固定资产：<input type="text" name="MachineName" id="MachineName" class="span2"  value="{tpl:$param.MachineName/}"/>
			资产型号：<input type="text" name="Version" id="Version" class="span2"  value="{tpl:$param.Version/}"/>
			<br/>
			选择机房
			<select class="clear" name = "DepotId" id = "Depot" onchange="GetCageList()">
			<option value = 0 >全部</option>
			{tpl:loop $DepotList $key $depot}
			<option value = {tpl:$key/} {tpl:if ($key==$DepotId)}selected{/tpl:if}>{tpl:$depot.name/}</option>
			{/tpl:loop}
			</select>
			所在机柜:<select name = "CageId" id = "Cage" class="span2">
				<option value = 0>全部</option>
				{tpl:loop $CageList $depot $depot_info}
					{tpl:if ($depot==$DepotId)}
						{tpl:loop $depot_info $cage $cage_info}
						<option value ={tpl:$cage/} {tpl:if ($cage==$CageId)}selected{/tpl:if}>{tpl:$cage_info.CageCode/}</option>
						{/tpl:loop}
				{/tpl:if}
				{/tpl:loop}
				</select>
			设备类型：<select class="span2" name="Flag" id="Flag">
			<option value ="8" >全部</option>
			{tpl:loop $FlagList $key $val}
			<option value = {tpl:$key/} {tpl:if ($key==$param.Flag)}selected{/tpl:if}>{tpl:$val/}</option>
			{/tpl:loop}
			</select>
			<br/>
			内网IP：<input type="text" name="LocalIP" id="LocalIP" class="span2" value="{tpl:$param.LocalIP/}"/>
			公网IP：<input type="text" name="WebIP" id="WebIP"class="span2" value="{tpl:$param.WebIP/}"/>
			
			使用人：<input type="text" name="User" id="User" class="span2" value="{tpl:$param.User/}"/> 
<input  type="submit" name="Submit" id="submit" value="查询" />
<input type="button" name="clear" id="clear" value="清空条件" />

</form>
<div style="float:right;">共有<span style="color:red;font-weight:bold;">{tpl:$count/}</span>条记录</div>
<table class="table table-bordered table-striped">
<tr>
<th align="center" class="rowtip">机器Id </th>
<th align="center" class="rowtip">序列号</th>
<th align="center" class="rowtip">资产编号</th>
<th align="center" class="rowtip">固定资产</th>
<th align="center" class="rowtip">资产型号</th>
<th align="center" class="rowtip">所属机房</th>
<th align="center" class="rowtip">所属机柜</th>

<th align="center" class="rowtip">设备类型</th>
<th align="center" class="rowtip">位置</th>
<th align="center" class="rowtip">机器高度</th>

<th align="center" class="rowtip">内网IP</th>  
<th align="center" class="rowtip">公网IP</th>

<th align="center" class="rowtip">实物状态</th>

<th align="center" class="rowtip">使用人</th>
<th align="center" class="rowtip">金额</th>
<th align="center" class="rowtip">用途</th>
<th align="center" class="rowtip">操作</th>
</tr>

{tpl:loop $MachineArr $Machine $machine_data}
<tr>
<td>{tpl:$machine_data.MachineId/}</td>
<td>{tpl:$machine_data.MachineCode/}</td>
<td>{tpl:$machine_data.EstateCode/}</td>
<td>{tpl:$machine_data.MachineName/}</td>
<td>{tpl:$machine_data.Version/}</td>
<td>{tpl:$machine_data.DepotName/}--{tpl:$machine_data.CageX/}</td>
<td>{tpl:$machine_data.CageCode/}</td>

<td>{tpl:$machine_data.Flag/}</td>
<td>{tpl:$machine_data.Position/}</td>
<td>{tpl:$machine_data.Size/}</td>

<td>{tpl:$machine_data.LocalIP/}</td>
<td>{tpl:$machine_data.WebIP/}</td>

<td>{tpl:$machine_data.MachineStatus/}</td>

<td>{tpl:$machine_data.User/}</td>
<td>{tpl:$machine_data.Comment.Money/}元</td>
<td>{tpl:$machine_data.Purpose/}</td>
<td><a href="javascript:;" onclick="machineModify('{tpl:$machine_data.MachineId/}');">修改</a>
|<a href="javascript:;" onclick="machineInfo('{tpl:$machine_data.MachineId/}');">详情</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$machine_data.MachineId/}','{tpl:$machine_data.MachineCode/}')">删除</a>
</td>
</tr>

{/tpl:loop}
</table>
{tpl:$page_content/}
</fieldset>


<script type="text/javascript">
$(function(){

	//GetCageList();
	//GetPartnerList();
	//清空所有条件
	$("#clear").click(function(){
		$(":input").val("");
		$("#submit").val("查询");
		$("#clear").val("清空条件");
	});

})
function GetCageList()
{
	var DepotId=$("#Depot").val();

	$.ajax
	({
		type:"GET",
		url:"?ctl=config/machine&ac=get.cage.list&DepotId="+DepotId,
		success:function(data)
		{
			var str = "<option value = 0 >全部</option>"+data;
			$("#Cage").html(str);		
		}
	
	})
}

</script>
{tpl:tpl contentFooter/}
