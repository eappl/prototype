{tpl:tpl contentHeader/}
<style>
#form th{align:center;}
.arrow{float:right;display:block;background-image:url(/img/splashy/grap_arrow.png);background-repeat:none;width:24px;height:22px;}
.asc{background-position:0 0;}
.desc{background-position:0 -22px;}
form{ margin:0;}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_machine').click(function(){
		addMachineBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加机器', width:700, height:670});
	});
});
function machineModify(mid){
	modifyMachineBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&MachineId=' + mid, {title:'修改机器', width:750, height:670});
}
function machineInfo(mid){
	MachineInfoBox = divBox.showBox('{tpl:$this.sign/}&ac=get.machine.info&MachineId=' + mid, {title:'机器详情', width:450, height:500});	
}
function promptDelete(m_id,MachineCode){

	deleteMachineBox = divBox.confirmBox({content:'是否删除 ' + MachineCode + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&MachineId=' + m_id+"&Flag=1";}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_machine">添加机器</a> ]&nbsp;&nbsp;&nbsp;&nbsp;
{tpl:$export_var/}
</fieldset>
<fieldset>
 
<legend>机器列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">序列号：<input type="text" name="MachineCode" id="MachineCode" class="span2" value="{tpl:$param.MachineCode/}"/>
			资产编号：<input type="text" name="EstateCode" id="EstateCode" class="span2"  value="{tpl:$param.EstateCode/}"/>
			固定资产：<input type="text" name="MachineName" id="MachineName" class="span2"  value="{tpl:$param.MachineName/}"/>
			资产型号：<input type="text" name="Version" id="Version" class="span2"  value="{tpl:$param.Version/}"/>
			<br/>
			所在机房
			<select class="span2" name = "DepotId" id = "Depot" onchange="GetCageList()">
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
			
		游戏:
<select name = "AppId" id = "AppId" onchange="getpermittedweeparter()">
	<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
	{tpl:loop $permitted_app $key $app}
<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
{/tpl:loop}
</select>
平台:
<select name = "PartnerId" id = "PartnerId" onchange="getpermittedserver()">
	<option value = 0 {tpl:if (0==$PartnerId)}selected{/tpl:if}> 全部 </option>
	 {tpl:loop $permitted_partner $partner_key $partner}
			<option value = {tpl:$partner_key/} {tpl:if ($partner_key==$PartnerId)}selected{/tpl:if}>{tpl:$partner.name/}</option>
	 {/tpl:loop}
</select>
服务器:
<select name = "ServerId" id = "ServerId">
	<option value = 0 {tpl:if (0==$ServerId)}selected{/tpl:if}> 全部 </option>
	{tpl:loop $permitted_server $server_key $server}
		<option value = {tpl:$server_key/} {tpl:if ($server_key==$ServerId)}selected{/tpl:if}>{tpl:$server.name/}</option>
	{/tpl:loop}
</select>
				<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
				<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
				<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
				<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
				<br/>
			内网IP：<input type="text" name="LocalIP" id="LocalIP" class="span2" value="{tpl:$param.LocalIP/}"/>
			公网IP：<input type="text" name="WebIP" id="WebIP" class="span2" value="{tpl:$param.WebIP/}"/>
			使用人：<input type="text" name="User" id="User" class="span2" value="{tpl:$param.User/}"/>
			<input type="hidden" name="field" id="field" value="{tpl:$param.field/}"/>
			<input type="hidden" name="order" id="order" value="{tpl:$param.order/}"/>
<input type="submit" id="commit" name="Submit"  value="查询" />
<input type="button" id="clear" name="reset" value="清空条件" />
</form>
<div style="float:right;">共有<span style="color:red;font-weight:bold;">{tpl:$count/}</span>条记录</div>
<table class="table table-bordered table-striped">
<tr><!----><!--<i class="splashy-arrow_state_blue_expanded"></i>-->
{tpl:if ($Show.MachineId)}<th val="MachineId" >机器Id <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.MachineCode)}<th val="MachineCode" >序列号 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.EstateCode)}<th val="EstateCode">资产编号 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.MachineName)}<th val="MachineName">固定资产 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.Version)}<th val="Version">资产型号 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.DepotId)}<th>机房 </th>{/tpl:if}
{tpl:if ($Show.CageId)}<th val="CageId">机柜 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}

{tpl:if ($Show.AppId)}<th>所属游戏</th>{/tpl:if}
{tpl:if ($Show.PartnerId)}<th>所属平台</th>{/tpl:if}
{tpl:if ($Show.ServerId)}<th val="ServerId">服务器 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}

{tpl:if ($Show.Position)}<th val="Position">位置 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.Size)}<th val="Size">机器U高 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.Current)}<th val="Current">额定电流 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}

{tpl:if ($Show.LocalIP)}<th val="LocalIP">内网IP <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if} 
{tpl:if ($Show.WebIP)}<th val="WebIP">公网IP <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}

{tpl:if ($Show.MachineStatus)}<th val="MachineStatus">实物状态 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.Status)}<th>实物标签</th>{/tpl:if}
{tpl:if ($Show.IntellectProperty)}<th val="IntellectProperty">知识产权 <a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}

{tpl:if ($Show.User)}<th val="User">使用人<a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.Purpose)}<th>用途</th>{/tpl:if}
{tpl:if ($Show.Money)}<th>金额</th>{/tpl:if}

{tpl:if ($Show.Cpu)}<th>CPU</th>{/tpl:if}
{tpl:if ($Show.CpuCount)}<th>CPU数量</th>{/tpl:if}
{tpl:if ($Show.Memory)}<th>内存</th>{/tpl:if}
{tpl:if ($Show.MemoryCount)}<th>内存数量</th>{/tpl:if}
{tpl:if ($Show.Hd)}<th>硬盘</th>{/tpl:if}
{tpl:if ($Show.HdCount)}<th>硬盘数量</th>{/tpl:if}
{tpl:if ($Show.HdMode)}<th>硬盘模式</th>{/tpl:if}
{tpl:if ($Show.Netcard)}<th>网卡</th>{/tpl:if}
{tpl:if ($Show.NetcardCount)}<th>网卡数量</th>{/tpl:if}

{tpl:if ($Show.Udate)}<th val="Udate">最新更新时间<a href="javascript:void(0)" class="arrow desc"></a></th>{/tpl:if}
{tpl:if ($Show.Remark)}<th>备注</th>{/tpl:if}

<th>操作</th>
</tr>
{tpl:loop $MachineArr $Machine $machine_data}
<tr>
{tpl:if ($Show.MachineId)}<td>{tpl:$machine_data.MachineId/}</td>{/tpl:if}
{tpl:if ($Show.MachineCode)}<td>{tpl:$machine_data.MachineCode/}</td>{/tpl:if}
{tpl:if ($Show.EstateCode)}<td>{tpl:$machine_data.EstateCode/}</td>{/tpl:if}
{tpl:if ($Show.MachineName)}<td>{tpl:$machine_data.MachineName/}</td>{/tpl:if}
{tpl:if ($Show.Version)}<td>{tpl:$machine_data.Version/}</td>{/tpl:if}
{tpl:if ($Show.DepotId)}<td>{tpl:$machine_data.DepotName/}--{tpl:$machine_data.CageX/}</td>{/tpl:if}
{tpl:if ($Show.CageId)}<td>{tpl:$machine_data.CageCode/}</td>{/tpl:if}

{tpl:if ($Show.AppId)}<td>{tpl:$machine_data.AppName/}</td>{/tpl:if}
{tpl:if ($Show.PartnerId)}<td>{tpl:$machine_data.PartnerName/}</td>{/tpl:if}
{tpl:if ($Show.ServerId)}<td>{tpl:$machine_data.ServerName/}</td>{/tpl:if}

{tpl:if ($Show.Position)}<td>{tpl:$machine_data.Position/}</td>{/tpl:if}
{tpl:if ($Show.Size)}<td>{tpl:$machine_data.Size/}</td>{/tpl:if}
{tpl:if ($Show.Current)}<td>{tpl:$machine_data.Current/}A</td>{/tpl:if}

{tpl:if ($Show.LocalIP)}<td>{tpl:$machine_data.LocalIP/}</td>{/tpl:if}
{tpl:if ($Show.WebIP)}<td>{tpl:$machine_data.WebIP/}</td>{/tpl:if}

{tpl:if ($Show.MachineStatus)}<td>{tpl:$machine_data.MachineStatus/}</td>{/tpl:if}
{tpl:if ($Show.Status)}<td>{tpl:$machine_data.Comment.Status/}</td>{/tpl:if}
{tpl:if ($Show.IntellectProperty)}<td>{tpl:$machine_data.IntellectProperty/}</td>{/tpl:if}


{tpl:if ($Show.User)}<td>{tpl:$machine_data.User/}</td>{/tpl:if}
{tpl:if ($Show.Purpose)}<td>{tpl:$machine_data.Purpose/}</td>{/tpl:if}
{tpl:if ($Show.Money)}<td>{tpl:$machine_data.Comment.Money/}</td>{/tpl:if}

{tpl:if ($Show.Cpu)}<td>{tpl:$machine_data.Comment.Cpu/}</td>{/tpl:if}
{tpl:if ($Show.CpuCount)}<td>{tpl:$machine_data.Comment.CpuCount/}</td>{/tpl:if}
{tpl:if ($Show.Memory)}<td>{tpl:$machine_data.Comment.Memory/}</td>{/tpl:if}
{tpl:if ($Show.MemoryCount)}<td>{tpl:$machine_data.Comment.MemoryCount/}</td>{/tpl:if}
{tpl:if ($Show.Hd)}<td>{tpl:$machine_data.Comment.Hd/}</td>{/tpl:if}
{tpl:if ($Show.HdCount)}<td>{tpl:$machine_data.Comment.HdCount/}</td>{/tpl:if}
{tpl:if ($Show.HdMode)}<td>{tpl:$machine_data.Comment.HdMode/}</td>{/tpl:if}
{tpl:if ($Show.Netcard)}<td>{tpl:$machine_data.Comment.Netcard/}</td>{/tpl:if}
{tpl:if ($Show.NetcardCount)}<td>{tpl:$machine_data.Comment.NetcardCount/}</td>{/tpl:if}

{tpl:if ($Show.Udate)}<td>{tpl:$machine_data.Udate/}</td>{/tpl:if}
{tpl:if ($Show.Remark)}<td>{tpl:$machine_data.Comment.Remark/}</td>{/tpl:if}

<td><a href="javascript:;" onclick="machineModify('{tpl:$machine_data.MachineId/}');">修改</a>
|<a href="javascript:;" onclick="machineInfo('{tpl:$machine_data.MachineId/}');">详情</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$machine_data.MachineId/}','{tpl:$machine_data.MachineCode/}')">删除</a>
</td>
</tr>
{/tpl:loop}

</table>
{tpl:$page_content/}
</fieldset>

 
</dl>

<script type="text/javascript">
$(function(){

	//GetCageList();
	//GetPartnerList();
	var field = $("#field").val();
	var order = $("#order").val();
	
	$("th[val='"+field+"'] a").removeClass().addClass("arrow "+order);
	//$("th[val='"+field+"'] a").text(order);
	//table th的样式
	$("#table th").addClass("rowtip");
	
	//清空form 
	$("#clear").click(function(){
		//$("#form").val("");
		$(":input").val("");
		$("#commit").val("查询");
		$("#clear").val("清空条件");
	});
		
	//th排序
	$(".desc").click(function(){
		var val = $(this).parent("th").attr("val");
		$(this).removeClass("desc").addClass("asc");
		$("#field").val(val);
		$("#order").val("asc");
		$("#form").submit();
	});
	$(".asc").click(function(){
		var val = $(this).parent("th").attr("val");
		$(this).removeClass("asc").addClass("desc");
		$("#field").val(val);
		$("#order").val("desc");
		$("#form").submit();
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
{tpl:tpl contentFooter/}
