{tpl:tpl contentHeader/}
<style>
td label{ display:inline;}
</style>
<fieldset><legend>选择显示的列表</legend>
<table class="table table-bordered table-striped" style="width:300px;">
<form action="{tpl:$this.sign/}&ac=show.add" name="form" id="show_add_form" method="post">
<tr>
<td><input type="checkbox" name="Show[MachineId]" value="1" {tpl:if ($Show.MachineId)}checked{/tpl:if} id="MachineId"/> <label for="MachineId">机器Id</label></td>
<td><input type="checkbox" name="Show[MachineCode]" value="1" {tpl:if ($Show.MachineCode)}checked{/tpl:if} id="MachineCode"/> <label for="MachineCode">序列号</label></td>

</tr>	
<tr>
<td><input type="checkbox" name="Show[DepotId]" value="1" {tpl:if ($Show.DepotId)}checked{/tpl:if} id="DepotId"/> <label for="DepotId">所属机房</label></td>
<td><input type="checkbox" name="Show[CageId]" value="1" {tpl:if ($Show.CageId)}checked{/tpl:if} id="CageId"/> <label for="CageId">所属机柜</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[Position]" value="1" {tpl:if ($Show.Position)}checked{/tpl:if} id="Position"/> <label for="Position">机柜位置</label></td>
<td><input type="checkbox" name="Show[Size]" value="1" id="Size" {tpl:if ($Show.Size)}checked{/tpl:if}/> <label for="Size">机器U高</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[Current]" value="1" id="Current" {tpl:if ($Show.Current)}checked{/tpl:if}/> <label for="Current">额定电流</label></td>
<td></td>
</tr>	

<tr>
<td><input type="checkbox" name="Show[AppId]" value="1" id="AppId" {tpl:if ($Show.AppId)}checked{/tpl:if}/> <label for="AppId">所属游戏</label></td>
<td><input type="checkbox" name="Show[PartnerId]" value="1" id="PartnerId" {tpl:if ($Show.PartnerId)}checked{/tpl:if}/> <label for="PartnerId">所属平台</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[ServerId]" value="1" id="ServerId" {tpl:if ($Show.ServerId)}checked{/tpl:if}/> <label for="ServerId">所属服务器</label></td>
<td></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[LocalIP]" value="1" id="LocalIP" {tpl:if ($Show.LocalIP)}checked{/tpl:if}/> <label for="LocalIP">内网IP</label></td>
<td><input type="checkbox" name="Show[WebIP]" value="1" id="WebIP" {tpl:if ($Show.WebIP)}checked{/tpl:if}/> <label for="WebIP">公网IP</label></td>
</tr>
<tr>
<td><input type="checkbox" name="Show[EstateCode]" value="1" id="EstateCode" {tpl:if ($Show.EstateCode)}checked{/tpl:if}/> <label for="EstateCode">资产编码</label></td>
<td><input type="checkbox" name="Show[MachineStatus]" value="1" id="MachineStatus" {tpl:if ($Show.MachineStatus)}checked{/tpl:if}/> <label for="MachineStatus">实物状态</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[MachineName]" value="1" id="MachineName" {tpl:if ($Show.MachineName)}checked{/tpl:if}/> <label for="MachineName">固定资产</label></td>
<td><input type="checkbox" name="Show[Version]" value="1" id="Version" {tpl:if ($Show.Version)}checked{/tpl:if}/> <label for="Version">资产型号</label></td>
</tr>	

<tr>
<td><input type="checkbox" name="Show[Status]" value="1" id="Status" {tpl:if ($Show.Status)}checked{/tpl:if}/> <label for="Status">实物标签</label></td>
<td><input type="checkbox" name="Show[IntellectProperty]" value="1" id="IntellectProperty" {tpl:if ($Show.IntellectProperty)}checked{/tpl:if}/> <label for="IntellectProperty">知识产权</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[User]" value="1" id="User" {tpl:if ($Show.User)}checked{/tpl:if}/> <label for="User">使用人</label></td>
<td><input type="checkbox" name="Show[Purpose]" value="1" id="Purpose" {tpl:if ($Show.Purpose)}checked{/tpl:if}/> <label for="Purpose">用途</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[Money]" value="1" id="Money" {tpl:if ($Show.Money)}checked{/tpl:if}/> <label for="Money">金额</label></td>
<td></td>
</tr>
<tr>
<td><input type="checkbox" name="Show[Cpu]" value="1" id="Cpu" {tpl:if ($Show.Cpu)}checked{/tpl:if}/> <label for="Cpu">CPU</label></td>
<td><input type="checkbox" name="Show[CpuCount]" value="1" id="CpuCount" {tpl:if ($Show.CpuCount)}checked{/tpl:if}/> <label for="CpuCount">CPU数量</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[Memory]" value="1" id="Memory" {tpl:if ($Show.Memory)}checked{/tpl:if}/> <label for="Memory">内存</label></td>
<td><input type="checkbox" name="Show[MemoryCount]" value="1" id="MemoryCount" {tpl:if ($Show.MemoryCount)}checked{/tpl:if}/> <label for="MemoryCount">内存数量</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[Hd]" value="1" id="Hd" {tpl:if ($Show.Hd)}checked{/tpl:if}/> <label for="Hd">硬盘</label></td>
<td><input type="checkbox" name="Show[HdCount]" value="1" id="HdCount" {tpl:if ($Show.HdCount)}checked{/tpl:if}/> <label for="HdCount">硬盘数量</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[HdMode]" value="1" id="HdMode" {tpl:if ($Show.HdMode)}checked{/tpl:if}/> <label for="HdMode">硬盘模式</label></td>
<td></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[Netcard]" value="1" id="Netcard" {tpl:if ($Show.Netcard)}checked{/tpl:if}/> <label for="Netcard">网卡</label></td>
<td><input type="checkbox" name="Show[NetcardCount]" value="1" id="NetcardCount" {tpl:if ($Show.NetcardCount)}checked{/tpl:if}/> <label for="NetcardCount">网卡数量</label></td>
</tr>	
<tr>
<td><input type="checkbox" name="Show[Udate]" value="1" id="Udate" {tpl:if ($Show.Udate)}checked{/tpl:if}/> <label for="Udate">最新更新时间</label></td>
<td><input type="checkbox" name="Show[Remark]" value="1" id="Remark" {tpl:if ($Show.Remark)}checked{/tpl:if}/> <label for="Remark">备注</label></td>
</tr>	
<tr>
<td>
<input type="submit" id="show_add_submit" name="Submit" value="提交" />
</td>
<td></td>
</tr>

</form>

</table>
</fieldset>
<script type="text/javascript">

$('#show_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[2] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}&ac=show.list');}});
			}
		}
	};
	$('#show_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}