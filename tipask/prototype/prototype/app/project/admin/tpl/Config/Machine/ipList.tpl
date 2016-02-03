{tpl:tpl contentHeader/}
<fieldset><legend>操作</legend>
{tpl:$export_var/}
</fieldset>
<fieldset><legend>机柜列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}&ac=ip.list" name="form" id="form" method="post">
			
			选择机房
			<select name = "DepotId" id = "Depot" onchange="GetCageList()">
			<option value = 0 >全部</option>
			{tpl:loop $DepotList $key $depot}
			<option value = {tpl:$key/} {tpl:if ($key==$DepotId)}selected{/tpl:if}>{tpl:$depot.name/}</option>
			{/tpl:loop}
			</select>			
<input  class="marright20" type="submit" name="Submit" value="查询" />
</tr>
</form>
<tr>
<th align="center" class="rowtip">机器Id</th>
<th align="center" class="rowtip">序列号</th>
<th align="center" class="rowtip">资产编号</th>
<th align="center" class="rowtip">内网IP</th>
<th align="center" class="rowtip">外网IP</th>
<th align="center" class="rowtip">用途</th>
</tr>

{tpl:loop $MachineArr $Machine $machine_data}
<tr>
<td>{tpl:$machine_data.MachineId/}</td>
<td>{tpl:$machine_data.MachineCode/}</td>
<td>{tpl:$machine_data.EstateCode/}</td>
<td>{tpl:$machine_data.LocalIP/}</td>
<td>{tpl:$machine_data.WebIP/}</td>
<td>{tpl:$machine_data.Purpose/}</td>

</tr>
{/tpl:loop}
</table>
{tpl:$page_content/}
</fieldset>
{tpl:tpl contentFooter/}