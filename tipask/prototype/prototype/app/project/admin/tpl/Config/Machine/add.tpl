{tpl:tpl contentHeader/}
<style>
table .span4{display:inline;}
table .span3{display:inline;}
</style>
<div class="br_bottom"></div>
<form name="machine_add_form" id="machine_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
<table  align="center" class="table table-bordered table-striped">
<tr>
<td>序列号 <input type="hidden" name='Flag' id='Flag' value='1'/></td>
<td colspan="3"><input type="text" name="MachineCode" id="MachineCode" class="span4" onblur="CheckMachineCode()" value=""/>
<span id="MachineCodeTip" style="color:red;"></span>
</td>
</tr>

<tr>
			<td>所在机房</td>
			<td>
				<select name = "Depot" id = "DepotName" onchange="GetCageList()">
				{tpl:loop $DepotList $key $depot}
				<option value = {tpl:$key/}>{tpl:$depot.name/}</option>
				{/tpl:loop}
				</select>
			</td>
			<td style="text-align:center">选择机柜</td>	
			<td><select name = "CageId" id = "CageId" onchange="GetCagePosition()">
				<option value ="">--选择机柜--</option>
				</select></td>				
</tr>

<tr>
			<td>所在机柜位置</td>
			<td>
				<select name = "Position" id = "Position" onchange="GetMachineSize()">
				<option value ="">--选择机柜位置--</option>
				</select>
		  </td>
		  <td>机器U高</td>
			<td><select name = "Size" id = "Size">
						<option value ="">--机器U高--</option>
						</select></td>		
</tr>

<tr>
<td>额定电流</td>
<td colspan="3"><input type="text" name="Current" id="Current" class="span3" onblur="CheckCurrent()" value=""/>A
 <input type="hidden" id="UserCurrent" value=""/><span id="CurrentTip"  style="color:red;"></span></td>
</tr>

<tr>
<td>内网ip</td>
<td><input type="text" name="LocalIP" id="LocalIP" class="span3 IP" value=""/> 
<span id="LocalIPTip" style="color:red"></span>
</td>
<td>公网ip</td>
<td><input type="text" name="WebIP" id="WebIP" class="span3 IP"  value=""/>
<span id="WebIPTip" style="color:red"></span>
</td>
</tr>

<tr>
			<td>选择游戏:</td>
			<td>			
				<select name="AppId" id="AppId" onchange="getpermittedweeparter()">				
				{tpl:loop $permitted_app $key $app}
				<option value ="{tpl:$key/}" >{tpl:$app.name/}</option>
				{/tpl:loop}
				<option value="other" >其它</option>
				</select>
			</td>
			<td class="PartnerTd">选择平台:</td>
			
			<td class="PartnerTd">
				<select name="PartnerId" id="PartnerId" onchange="getpermittedserver()">
				<option value ="">--选择平台--</option>
				</select>
			</td>		
			<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
			<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
			<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
			<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
</tr>
		
<tr>
<td>所在服务器</td>
<td id='ServerTd' colspan="3"><select name = "ServerId" id = "ServerId" >
				<option value ="">--选择服务器--</option>
				</select> </td>
</tr>

<tr>
<td>资产编号</td>
<td><input type="text" name="EstateCode" id="EstateCode" class="span3"  value="" onblur="CheckEstateCode()"/> 
<span id="EstateCodeTip" style="color:red;"></span> </td>
<td>实物状态</td>
<td><input type="text" name="MachineStatus" id="MachineStatus" class="span3" value=""/>*</td>
</tr>

<tr>
<td>固定资产</td>
<td><input type="text" name="MachineName" id="MachineName" class="span3"  value=""/> *</td>
<td>资产型号</td>
<td><input type="text" name="Version" id="Version" class="span3"  value=""/>*</td>
</tr>

<tr>
	<td>实物标签</td>
	<td>
		<select name="Comment[Status]" id='Comment[Status]'>
		{tpl:loop $StatusList $key $val}
					<option value = {tpl:$key/} >{tpl:$val/}</option>
		{/tpl:loop}
		</select>
	</td>
	<td>知识产权</td>
	<td><select name="IntellectProperty" id="IntellectProperty">
	{tpl:loop $IntellectPropertyList $key $val}
				<option value = {tpl:$key/} >{tpl:$val/}</option>
	{/tpl:loop}
	</select></td>
</tr>


<tr>
<td>使用人</td>
<td><input type="text" name="User" id="User" class="span3"  value=""/> * </td>
<td>金额</td>
<td><input type="text" name="Comment[Money]" id="Comment[Money]" class="span3" value="" onblur="checkMoney()"/>元</td>
</tr>

<tr>
<td>CPU</td>
<td><input  type="text"  name="Comment[Cpu]" id="Comment[Cpu]" class="span3"  value=""/></td>
<td>CPU数量</td>
<td><input  type="text"  name="Comment[CpuCount]" id="Comment[CpuCount]" class="span3"  value=""/></td>
</tr>

<tr>
<td>内存</td>
<td><input  type="text" name="Comment[Memory]" id="Comment[Memory]" class="span3"  value=""/></td>
<td>内存数量</td>
<td><input  type="text" name="Comment[MemoryCount]" id="Comment[MemoryCount]" class="span3"  value=""/></td>
</tr>
  
<tr>
<td>硬盘</td>
<td><input type="text" name="Comment[Hd]" id="Comment[Hd]" class="span3"  value=""/></td>
<td>硬盘数量</td>
<td><input type="text" name="Comment[HdCount]" id="Comment[HdCount]" class="span3"  value=""/></td>
</tr>

<tr>
<td>硬盘模式</td>
<td colspan='3'><input type="text" name="Comment[HdMode]" id="Comment[HdMode]" class="span3"  value=""/> </td>
</tr>

<tr>
<td>网卡</td>
<td><input type="text" name="Comment[Netcard]" id="Comment[Netcard]" class="span3"  value=""/></td>
<td>网卡数量</td>
<td><input type="text" name="Comment[NetcardCount]" id="Comment[NetcardCount]" class="span3"  value=""/></td>
</tr>

<tr>
<td>用途</td>
<td colspan='3'><input type="text" name="Purpose" id="Purpose" class="span5"  value=""/> </td>
</tr>

<tr>
<td>备注</td><!-- <input type="text" name="Comment[Remark]" id="Comment[Remark]" class="span4"  value=""/>-->
<td colspan='3'><textarea style="width:500px;"  rows="3" cols="20"  name="Comment[Remark]" id="Comment[Remark]">
</textarea>
</td>
</tr>

<tr class="noborder"><td></td>
		<td colspan='3'><button type="submit" id="machine_add_submit">提交</button></td>
		</tr>
</table>
</form>
<script type="text/javascript">

$(function(){
	$('#machine_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {
				//var LocalIP = $("#LocalIP").val();
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
					//errors[2] = '失败，必须输入机器编码';
					errors[3] = '失败，机器编码已存在';
					errors[4] = '失败，必须输入机柜';
					//errors[5] = '失败，必须输入额定电流';
					errors[6] = '失败，输入的额定电流大于剩余电流';
					errors[7] = '失败，必须输入机器尺寸';
					
					errors[8] = '失败，必须输入机器开始位置';
					errors[9] = '失败，填写的尺寸大于剩余尺寸';
					errors[10] = '失败，内网ip已存在';
					errors[11] = '失败，公网ip已存在';
					errors[12] = '失败，未选择游戏服务器';
					errors[13] = '失败，必须输入固定资产';
					errors[14] = '失败，必须输入资产型号';
					errors[15] = '失败，必须输入实物状态';
					errors[16] = '失败，资产编码已存在';
					errors[17] = '失败，请修正后再次提交'; 
				
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加机器成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
				
				
				
			}
		};
		$('#machine_add_form').ajaxForm(options);
	});
	GetCageList();
	//GetPartnerList();
	getpermittedweeparter();
	$("#CpuCount,#MemoryCount,#HdCount,#NetcardCount").blur(function(){
		var val = $(this).val();
		if(val < 0)
		{
			alert("数量不能小于0");		
		}
	})
	
	//cpu.memory,hd 
	$(".addbutton").click(function(){
		$(this).parent('td').next('td').find("input").show();
	
	})
	
	//检查IP是否存在
	$(".IP").blur(function(){
		var type = $(this).attr("name");
		var val = $(this).val();
		if(val != "" && type != "")
		{
			$.ajax
			({
				type:"GET",
				url:"?ctl=config/machine&ac=check.ip&type="+type+"&ip="+val,
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
	var DepotId=$("#DepotName").val();
	
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
			$("#Position").html(data.option);
			$("#UserCurrent").val(data.current);
			$("#CurrentTip").html("<span style='color:red'>可用电流"+data.current+"A</span>");
			GetMachineSize();
		}
	})
}
function GetMachineSize()
{
	var CageId=$("#CageId").val();
	var PositionId=$("#Position").val();
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
		var ServerStr = "<input type='text' class='span3' name='ServerId' id='ServerId'>* <span style='color:red;'>请填写服务器Id,多个以英文,分割</span>";
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
		$.ajax
		({
			type:"GET",
			url:"?ctl=config/machine&ac=check.machine.code&MachineCode="+MachineCode,
			success:function(data)
			{
				if(data == 'no')
				{
					$("#MachineCodeTip").html("此编码已存在，请重新添加");		
				}	else{
					$("#MachinecodeTip").html("");		
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
		$.ajax
		({
			type:"GET",
			url:"?ctl=config/machine&ac=check.estate.code&EstateCode="+EstateCode,
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
	
	if(val!="" && val!='无')
	{
		var userVal = $("#UserCurrent").val();
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