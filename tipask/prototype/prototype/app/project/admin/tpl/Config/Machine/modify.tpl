{tpl:tpl contentHeader/}
<style>
table .span4{display:inline;}
table .span3{display:inline;}
table th,table td{white-space:nowrap;}
</style>
<div class="br_bottom"></div>
<form name="machine_modify_form" id="machine_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
<table align="center" class="table table-bordered table-striped">
<tr>
<td>序列号<input type="hidden" name="Flag" id="Flag" value="1"/></td>
<td colspan="3"><input type="text" name="MachineCode" id="MachineCode" value="{tpl:$MachineInfo.MachineCode/}" class="span4" onblur="CheckMachineCode()"/>
<span id="MachineCodeTip" style="color:red;"></span>
</td>
</tr>

<tr>
			<td>所在机房</td>
			<td>
				<select name = "DepotId" id = "DepotId" onchange="GetCageList()">
				{tpl:loop $DepotList $key $depot}
				<option value ="{tpl:$key/}" {tpl:if ($key==$MachineInfo.DepotId)}selected{/tpl:if}>{tpl:$depot.name/}</option>
				{/tpl:loop}
				</select>
			</td>
			<td>选择机柜</td>	
			<td><select name = "CageId" id = "CageId" onchange="GetCagePosition()">
			
			{tpl:loop $CageList $depot $depot_info}
					{tpl:if ($depot==$MachineInfo.DepotId)}
						{tpl:loop $depot_info $cage $cage_info}
						<option value ={tpl:$cage/} {tpl:if ($cage==$MachineInfo.CageId)}selected{/tpl:if}>{tpl:$cage_info.CageCode/}</option>
						{/tpl:loop}
				{/tpl:if}
				{/tpl:loop}
								
				</select></td>				
</tr>

<tr>
			<td>机柜位置</td>
			<td>
				<select name = "Position" id = "PositionId" onchange="GetMachineSize()">
				{tpl:loop $PositionList $key $val}
				<option value = "{tpl:$key/}" {tpl:if ($key==$MachineInfo.Position)}selected{/tpl:if}>行{tpl:$key/}</option>
				{/tpl:loop}
				</select>
		  </td>
		  <td>机器U高</td>
			<td><select name = "Size" id = "Size">
				{tpl:loop $SizeList $key $val}
				<option value="{tpl:$key/}"{tpl:if ($key==$MachineInfo.Size)}selected{/tpl:if}>{tpl:$key/}个空间</option>
				{/tpl:loop}
			</select></td>		
</tr>

<tr>
<td>额定电流</td>
<td colspan="3"><input type="text" name="Current" id="Current" class="span3" onblur="CheckCurrent()" value="{tpl:$MachineInfo.Current/}"/>A
 <input type="hidden" id="UseCurrent" value="{tpl:$MachineInfo.UseCurrent/}"/>
 <span id="currentTip"  style="color:red"></span></td>
</tr>

<tr>
<td>内网ip</td>
<td><input type="text" name="LocalIP" id="LocalIP" class="span3 IP"  value="{tpl:$MachineInfo.LocalIP/}"/>
<span id="LocalIPTip" style="color:red"></span>
</td>
<td>公网ip</td>
<td><input type="text" name="WebIP" id="WebIP" class="span3 IP"  value="{tpl:$MachineInfo.WebIP/}"/>
<span id="WebIPTip" style="color:red"></span>
</td>
</tr>

<tr>
			<td>选择游戏</td>
			<td>			
				<select name = "AppId" id = "AppId" onchange="getpermittedweeparter()" style="margin-right:60px;">				
				{tpl:loop $permitted_app $key $app}
				<option value="{tpl:$key/}" {tpl:if ($key==$MachineInfo.AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
				{/tpl:loop}
				<option value="other" {tpl:if ('other'==$MachineInfo.AppId)}selected{/tpl:if}>其它</option>
				</select>
			</td>
			<td class="PartnerTd">选择平台</td>
			
			<td class="PartnerTd">
				<select name = "PartnerId" id = "PartnerId" onchange="getpermittedserver()">
				{tpl:loop $permitted_partner $partner_key $partner}
					<option value = {tpl:$partner_key/} {tpl:if ($partner_key==$MachineInfo.PartnerId)}selected{/tpl:if}>{tpl:$partner.name/}</option>
			  {/tpl:loop}
				</select>
			</td>		
</tr>
		
<tr>
<td>所在服务器</td>
<td colspan="3" id="ServerTd"><select name = "ServerId" id = "ServerId" >
				{tpl:loop $permitted_server $server_key $server}
		<option value = {tpl:$server_key/} {tpl:if ($server_key==$MachineInfo.ServerId)}selected{/tpl:if}>{tpl:$server.name/}</option>
	{/tpl:loop}
				</select> </td>
			<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
			<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
			<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
			<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
</tr>

<tr>
<td>资产编号</td>
<td><input type="text" name="EstateCode" id="EstateCode" class="span3"  value="{tpl:$MachineInfo.EstateCode/}" onblur="CheckEstateCode()"/> 
<span id="EstateCodeTip" style="color:red;"></span></td>
<td>实物状态</td>
<td><input type="text" name="MachineStatus" id="MachineStatus" class="span3"  value="{tpl:$MachineInfo.MachineStatus/}"/>*</td>
</tr>

<tr>
<td>固定资产</td>
<td><input type="text" name="MachineName" id="MachineName" class="span3"  value="{tpl:$MachineInfo.MachineName/}"/>*</td>
<td>资产型号</td>
<td><input type="text" name="Version" id="Version" class="span3"  value="{tpl:$MachineInfo.Version/}"/>*</td>
</tr>

<tr>
	<td>实物标签</td>
	<td>
		<select name="Comment[Status]" id='Comment[Status]'>
		{tpl:loop $StatusList $key $val}
					<option value ="{tpl:$key/}" {tpl:if ($key==$MachineInfo.Comment.Status)}selected{/tpl:if} >{tpl:$val/}</option>
		{/tpl:loop}
		</select>
	</td>
	<td>知识产权</td>
	<td><select name="IntellectProperty" id="IntellectProperty">
	{tpl:loop $IntellectPropertyList $key $val}
				<option value ="{tpl:$key/}" {tpl:if ($key==$MachineInfo.IntellectProperty)}selected{/tpl:if} >{tpl:$val/}</option>
	{/tpl:loop}
	</select></td>
</tr>

<tr>
<td>使用人</td>
<td><input type="text" name="User" id="User" class="span3"  value="{tpl:$MachineInfo.User/}"/> *</td>
<td>金额</td>
<td><input type="text" name="Comment[Money]" id="Comment[Money]" class="span3" value="{tpl:$MachineInfo.Comment.Money/}" onblur='checkMoney()'/>元</td>
</tr>

<tr>
<td>CPU</td>
<td><input type="text" name="Comment[Cpu]" id="Comment[Cpu]" class="span3"  value="{tpl:$MachineInfo.Comment.Cpu/}"/></td>
<td>CPU数量</td>
<td><input  type="text"  name="Comment[CpuCount]" id="Comment[CpuCount]" class="span3" value="{tpl:$MachineInfo.Comment.CpuCount/}"/></td>
</tr>

<tr>
<td>内存</td>
<td><input type="text" name="Comment[Memory]" id="Comment[Memory]" class="span3"  value="{tpl:$MachineInfo.Comment.Memory/}"/></td>
<td>内存数量</td>
<td><input  type="text" name="Comment[MemoryCount]" id="Comment[MemoryCount]" class="span3" value="{tpl:$MachineInfo.Comment.MemoryCount/}"/></td>
</tr>

<tr>
<td>硬盘</td>
<td><input type="text" name="Comment[Hd]" id="Comment[Hd]" class="span3"  value="{tpl:$MachineInfo.Comment.Hd/}"/></td>
<td>硬盘数量</td>
<td><input type="text" name="Comment[HdCount]" id="Comment[HdCount]" class="span3" value="{tpl:$MachineInfo.Comment.HdCount/}"/></td>
</tr>

<tr>
<td>硬盘模式</td>
<td colspan="3"><input type="text" name="Comment[HdMode]" id="Comment[HdMode]" class="span3"  value="{tpl:$MachineInfo.Comment.HdMode/}"/> </td>
</tr>

<tr>
<td>网卡</td>
<td><input type="text" name="Comment[Netcard]" id="Comment[Netcard]" class="span3"  value="{tpl:$MachineInfo.Comment.Netcard/}"/></td>
<td>网卡数量</td>
<td><input type="text" name="Comment[NetcardCount]" id="Comment[NetcardCount]" class="span3"  value="{tpl:$MachineInfo.Comment.NetcardCount/}"/></td>
</tr>

<tr>
<td>用途</td>
<td colspan='3'><input type="text" name="Purpose" id="Purpose" class="span5"  value="{tpl:$MachineInfo.Purpose/}"/> </td>
</tr>

<tr>
<td>备注</td><!-- <input type="text" name="Comment[Remark]" id="Comment[Remark]" class="span4"  value="{tpl:$MachineInfo.Comment.Remark/}"/>  -->
<td colspan="3"><textarea style="width:500px;"  rows="3" cols="20"  name="Comment[Remark]" id="Comment[Remark]">{tpl:$MachineInfo.Comment.Remark/}
</textarea></td>
</tr>

</tr>
		<tr class="noborder"><td><input type='hidden' name='MachineId' id="MachineId" value='{tpl:$MachineInfo.MachineId/}'/></td>
		<td colspan="3"><button type="submit" id="machine_modify_submit">提交</button></td>
		</tr>
</table>
</form>

<script type="text/javascript">
$(function(){
	$('#machine_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {
				var ServerId = $("#ServerId").val();				
				var MachineName = $("#MachineName").val();
				var Version = $("#Version").val();
				var MachineStatus = $("#MachineStatus").val();
				var User = $("#User").val();
				var mes = "";
				
				if(ServerId == "" || ServerId == 0)
					mes+="必须输入游戏服务器<br/>";
				if(MachineName == "")
					mes+="必须输入固定资产<br/>";
				if(Version == "")
					mes+="必须输入资产型号<br/>";
				if(MachineStatus == "")
					mes+="必须输入实物状态<br/>";
				if(User == "")
					mes+="必须输入使用人<br/>";
				if(mes!="")
				{
						divBox.alertBox(mes,function(){});
						return false;
				}
			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[3] = '失败，机器编码已存在';
					errors[4] = '失败，必须输入机柜';
					errors[6] = '失败，输入的额定电流大于剩余电流';
					errors[7] = '失败，必须输入机器尺寸';
					
					errors[8] = '失败，必须输入机器开始位置';
					errors[9] = '失败，填写的尺寸大于剩余尺寸';
					errors[10] = '失败，内网ip已存在';
					errors[11] = '失败，公网ip已存在';
					
				  errors[12] = '失败，未选择游戏服务器';
					errors[13] = '失败，必须输入固定资产';
					errors[14] = '失败，必须输入资产型号';
					errors[16] = '失败，资产编码已存在';
					errors[15] = '失败，必须输入实物状态';
					errors[17] = '失败，请修正后再次提交'; 
					 
				  divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '机器修改成功';
				  divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
				
				
				
			}
		};
		$('#machine_modify_form').ajaxForm(options);
	});
	
	var AppId = $("#AppId").val();
	if(AppId=="other")
	{
		
		$(".PartnerTd").hide();
		var ServerStr = "<input type='text' class='span3' name='ServerId' id='ServerId' value='{tpl:$MachineInfo.ServerId/}'>* </span>";
		$("#ServerTd").html(ServerStr);
	}
	
	$("#CpuCount,#MemoryCount,#HdCount,#NetcardCount").blur(function(){
		var val = $(this).val();
		if(val < 0)
		{
			alert("数量不能小于0");		
		}
	})
	
	//检查IP是否存在
	$(".IP").blur(function(){
		var type = $(this).attr("name");
		var val = $(this).val();
		var MachineId = $("#MachineId").val();
		if(val != "" && type != "")
		{
			$.ajax
			({
				type:"GET",
				url:"?ctl=config/machine&ac=check.ip&type="+type+"&ip="+val+"&MachineId="+MachineId,
				success:function(data)
				{
					if(data == 'no')
					{
						$("#"+type+"Tip").html("此IP已存在，请重新添加");		
					}	else{
						$("#"+type+"Tip").html("");		
					}	
				}
			})	
		}
		
	});
	
});

function GetCageList()
{
	var DepotId=$("#DepotId").val();
	$.ajax
	({
		type:"GET",
		url:"?ctl=config/machine&ac=get.cage.list&DepotId="+DepotId,
		success:function(data)
		{
			$("#CageId").html(data);
			GetCagePosition();
		}
	
	})
}
function GetCagePosition()
{
	var CageId=$("#CageId").val();
	$.ajax
	({
		type:"GET",
		dataType:'json',
		url:"?ctl=config/machine&ac=get.cage.position.list&CageId="+CageId,
		success:function(data)
		{
			$("#PositionId").html(data.option);
			$("#UserCurrent").val(data.current); //UserCurrent表示可用电流
			$("#currentTip").html("可用的电流"+data.current+"A");
			GetMachineSize();
		}
	})
}
function GetMachineSize()
{
	var CageId=$("#CageId").val(); 
	var PositionId=$("#PositionId").val();
	
	$.ajax
	({
		type:"GET",
		url:"?ctl=config/machine&ac=get.machine.size&CageId="+CageId+"&PositionId="+PositionId,
		success:function(data)
		{
		
			$("#Size").html(data);		
			
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
	AppId=$("#AppId");
	if(AppId.val()=="other")
	{		
		$(".PartnerTd").hide();
		var ServerStr = "<input type='text' class='span3' name='ServerId' id='ServerId' value='{tpl:$MachineInfo.ServerId/}'>* <span style='color:red;'>请填写服务器Id,多个以英文,分割</span>";
		$("#ServerTd").html(ServerStr);
	}else{	
		$(".PartnerTd").show();
		var ServerStr = "<select name = 'ServerId' id = 'ServerId' ></select> </td>";
		$("#ServerTd").html(ServerStr);
		partner_type=$("#partner_type");	
		is_abroad=$("#is_abroad");
		AreaId=$("#AreaId");
		$.ajax
		({
			type: "GET",
			url: "?ctl=config/permission&ac=get.partner&AppId="+AppId.val()+"&partner_type="+partner_type.val()+"&is_abroad="+is_abroad.val()+"&AreaId="+AreaId.val(),
			success: function(msg)
			{
				$("#PartnerId").html(msg);
				getpermittedserver();
			}
		});
	}
}

function CheckMachineCode()
{
	var MachineCode = $("#MachineCode").val();
	if(MachineCode != "")
	{
		var MachineId = $("#MachineId").val();
		$.ajax
		({
			type:"GET",
			url:"?ctl=config/machine&ac=check.machine.code&MachineCode="+MachineCode+"&MachineId="+MachineId,
			success:function(data)
			{
				if(data == 'no')
				{
					$("#MachineCodeTip").html("此编码已存在，请重新添加");		
				}else
				{
					$("#MachineCodeTip").html("");		
				}		
			}
		})
	}
}
function CheckEstateCode()
{
	var EstateCode = $("#EstateCode").val();
	if(EstateCode != "")
	{
		var MachineId = $("#MachineId").val();
		$.ajax
		({
			type:"GET",
			url:"?ctl=config/machine&ac=check.estate.code&EstateCode="+EstateCode+"&MachineId="+MachineId,
			success:function(data)
			{
				if(data == 'no')
				{
					$("#EstateCodeTip").html("此资产编码已存在，请重新添加");		
				}	else{
					$("#EstateCodeTip").html("");		
				}	
			}
		})
	}
}
function CheckCurrent()
{
	var val = $("#Current").val();
	if(val!="" || val!="无")
	{
		if(val<0)
		{
			alert("额定电流不能为负数");
		}else if(parseFloat(val) > parseFloat(userVal))
		{
			alert("额定电流不能大于可用电流");	
		}
	}
}
function checkMoney()
{
	var money = $("#Money").val();
	if(money!="" && money < 0)
	{	
		alert("金额不能为负数");		
	}	
}
</script>
{tpl:tpl contentFooter/}