{tpl:tpl contentHeader/}
<style>
table .span4{display:inline;}
table .span3{display:inline;}
</style>
<div class="br_bottom"></div>
<form name="machine_add_form" id="machine_add_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" width="99%">
<tr>
<td>序列号 <input type="hidden" name="Flag" value='5'/></td>
<td colspan="3"><input type="text" name="MachineCode" id="MachineCode" value="{tpl:$MachineInfo.MachineCode/}" onblur="CheckMachineCode()"/>
<span id="MachineCodeTip" style="color:red;"></span>
</td>
</tr>

<tr>
<td>所属机器序列号</td>
<td colspan="3">
<input type="text" name="OwnerCode" id="OwnerCode" class="span3" onblur="CheckOwnerCode()" value="{tpl:$MachineInfo.OwnerCode/}"/> 
<input type="hidden" name="Owner" id="Owner" value="{tpl:$MachineInfo.Owner/}"/>
</td>
</tr>

<tr class="hover removeTr">
	
			<td>所在机房</td>
			<td align="left">
				<select name = "Depot" id = "DepotName" onchange="GetCageList()">
				{tpl:loop $DepotList $key $depot}
				<option value = {tpl:$key/}>{tpl:$depot.name/}</option>
				{/tpl:loop}
				</select>
			</td>
			<td style="text-align:center">所在机柜</td>	
			<td><select name = "CageId" id = "CageId" onchange="GetCagePosition()">
			{tpl:loop $CageList $depot $depot_info}
					{tpl:if ($depot==$MachineInfo.DepotId)}
						{tpl:loop $depot_info $cage $cage_info}
						<option value ="{tpl:$cage/}" {tpl:if ($cage==$MachineInfo.CageId)}selected{/tpl:if}>{tpl:$cage_info.CageCode/}</option>
						{/tpl:loop}
				{/tpl:if}
				{/tpl:loop}
				</select></td>		
</tr>

<tr class="hover removeTr">
			<td>所在机柜位置</td>
			<td align="left">
				<select name = "Position" id = "PositionId" onchange="GetMachineSize()">
				{tpl:loop $PositionList $key $val}
				<option value = "{tpl:$key/}" {tpl:if ($key==$MachineInfo.Position)}selected{/tpl:if}>行{tpl:$key/}</option>
				{/tpl:loop}
				</select>
		  </td>
		  <td>设备U高</td>
			<td><select name = "Size" id = "Size">
				{tpl:loop $SizeList $key $val}
				<option value="{tpl:$key/}" {tpl:if ($key==$MachineInfo.Size)}selected{/tpl:if}>{tpl:$key/}个空间</option>
				{/tpl:loop}
			</select></td>			
</tr>

<tr>
<td>资产编号</td>
<td colspan="3"><input type="text" name="EstateCode" id="EstateCode" class="span3"  value="{tpl:$MachineInfo.EstateCode/}" onblur="CheckEstateCode()"/> 
<span id="EstateCodeTip" style="color:red;"></span> </td>
</tr>

<td>固定资产</td>
<td><input type="text" name="MachineName" id="MachineName" class="span3"  value="{tpl:$MachineInfo.MachineName/}"/> *</td>
<td>资产型号</td>
<td><input type="text" name="Version" id="Version" class="span3"  value="{tpl:$MachineInfo.Version/}"/></td>
<tr>


</tr>

<tr>
	<td>实物状态</td>
	<td><input type="text" name="MachineStatus" id="MachineStatus" class="span3"  value="{tpl:$MachineInfo.MachineStatus/}"/>*</td>
	<td>实物标签</td>
	<td>
		<select name="Comment[Status]" id='Comment[Status]'>
		{tpl:loop $StatusList $key $val}
					<option value ="{tpl:$key/}"{tpl:if ($key==$MachineInfo.Comment.Status)}selected{/tpl:if} >{tpl:$val/}</option>
		{/tpl:loop}
		</select>
	</td>

</tr>


<tr>
<td>使用人</td>
<td colspan="3"><input type="text" name="User" id="User" class="span3"  value="{tpl:$MachineInfo.User/}"/> *</td>
</tr>

<tr>
<td>金额</td>
<td colspan="3"><input type="text" name="Comment[Money]" id="Money" class="span3" onblur="checkMoney()"  value="{tpl:$MachineInfo.Comment.Money/}"/>元 </td>
</tr>

<tr>
<td>用途</td>
<td colspan="3"><input type="text" name="Purpose" id="Purpose" class="span5" value="{tpl:$MachineInfo.Purpose/}"/></td>
</tr>

<tr>
<td>备注</td><!--<input type="text" name="Comment[Remark]" id="Comment[Remark]" class="span3"  value="{tpl:$MachineInfo.Comment.Remark/}"/>-->
<td colspan="3"><textarea style="width:500px;"  rows="3" cols="20"  name="Comment[Remark]" id="Comment[Remark]">{tpl:$MachineInfo.Comment.Remark/}
</textarea>  </td>
</tr>

<tr class="noborder"><td><input type='hidden' name='MachineId' id="MachineId"  value="{tpl:$MachineInfo.MachineId/}"/></td>
		<td colspan="3"><button type="submit" id="machine_add_submit">提交</button></td>
		</tr>
</table>
</form>

<script type="text/javascript">

$(function(){
	$('#machine_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {
			
				var MachineName = $("#MachineName").val();
				var Version = $("#Version").val();
				var MachineStatus = $("#MachineStatus").val();
				var User = $("#User").val();
				var mes = "";
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
					errors[2] = '失败，必须输入机器编码';
					errors[3] = '失败，机器编码已存在';
					errors[4] = '失败，必须输入机柜';

					errors[7] = '失败，必须输入机器尺寸';
					
					errors[8] = '失败，必须输入机器开始位置';
					errors[9] = '失败，填写的尺寸大于剩余尺寸';
					
					errors[13] = '失败，必须输入固定资产';
					errors[14] = '失败，必须输入资产型号';
					errors[15] = '失败，必须输入实物状态';
					errors[18] = '失败，金额必须大于0';
					errors[16] = '失败，请修正后再次提交'; 
				
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '其他设备修改成功';
					//$.tplboxs.open(message, {title:'提示信息',onok:function(){document.location.href='{tpl:$this.sign/}&ac=network.list'+ '&DepotId=' + $("#Depot").val();}});
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}&Flag=5');}});
				}
				
				
				
			}
		};
		$('#machine_add_form').ajaxForm(options);
	});
	var OwnerCode = $("#OwnerCode").val();
	if(OwnerCode != "")
	{
		$(".removeTr").hide();	
	}
	
	//GetCageList();
	
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
			//$("#UserCurrent").val(data.current);
			//$("#currentTip").html("可用电流"+data.current+"A");
			GetMachineSize();
		}
	})
}
function GetMachineSize()
{
	var CageId=$("#CageId").val();
	var PositionId=$("#PositionId").val();
	//alert(PositionId);
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
function CheckOwnerCode()
{
	var OwnerCode = $("#OwnerCode").val();
	if(OwnerCode!= "")
	{
		$.ajax
		({
			type:"GET",
			url:"?ctl=config/machine&ac=check.owner.code&OwnerCode="+OwnerCode,
			success:function(data)
			{
				$("#Owner").remove();
				if(data=='no')
				{
					alert("您填的机器不存在");		
				}else
				{
					var data = eval("("+data+")");
					var positionList = "<input type='hidden' name='Owner' value='"+data['MachineId']+"'><input type='hidden' name='CageId' value='"+data['CageId']+"'><input type='hidden' name='Position' value='"+data['Position']+"'><input type='hidden' name='Size' value='"+data['Size']+"'>";
					$(".removeTr").remove();
					$("#OwnerCode").siblings().remove();//避免产生很多个隐藏框 
					$("#OwnerCode").after(positionList);
					
				}
			}
		})
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