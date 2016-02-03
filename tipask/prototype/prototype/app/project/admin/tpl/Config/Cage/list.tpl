{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_cage').click(function(){
		addCageBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加机柜',width:550,height:430});
	});
});
function cageModify(mid){
	modifyCageBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&CageId=' + mid, {title:'修改机柜',width:500, height:500});
}
function promptDelete(m_id,cageName){

	$.ajax
	({
		type:"GET",
		url:"?ctl=config/cage&ac=get.cage.delmes&CageId="+m_id,
		success:function(data)
		{	
			if(data==1)
			{
				divBox.alertBox("此机柜下有机器，不能删除！");
			}else{						
				deleteCageBox = divBox.confirmBox({content:'是否删除 ' + cageName + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&CageId=' + m_id;}});
			}
		}
	})
	
	
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_cage">添加机柜</a> ]
</fieldset>
<fieldset><legend>机柜列表</legend>

<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择机房
			<select name = "DepotId" id = "DepotId" class="span2">
			<option value = 0 {tpl:if (0==$DepotId)}selected{/tpl:if}>全部</option>
			{tpl:loop $DepotList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$DepotId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>

<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<table class="table table-bordered table-striped">
<tr>
<th align="center" class="rowtip">机柜ID</th>
<th align="center" class="rowtip">机柜编码</th>
<th align="center" class="rowtip">机器数量</th>
<th align="center" class="rowtip">所属机房</th>
<th align="center" class="rowtip">高度</th>
<th align="center" class="rowtip">额定电流</th>
<th align="center" class="rowtip">实际电流</th>
<th align="center" class="rowtip">备注</th>
<th align="center" class="rowtip">最新更新时间</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $CageListAll $Cage $cage_data}
<tr>
<td>{tpl:$cage_data.CageId/}</td>
<td>{tpl:$cage_data.CageCode/}</td>
<td>{tpl:$cage_data.MachineCount/}</td>
<td>{tpl:$cage_data.DepotName/}--{tpl:$cage_data.X/}</td>
<td>{tpl:$cage_data.Size/}U</td>
<td>{tpl:$cage_data.Current/}A</td>
<td>{tpl:$cage_data.ActualCurrent/}A</td>
<td>{tpl:$cage_data.Comment/}</td>
<td>{tpl:$cage_data.Udate/}</td>
<td><a href="javascript:;" onclick="cageModify('{tpl:$cage_data.CageId/}');">修改</a>
| <a  href="javascript:;" onclick="promptDelete('{tpl:$cage_data.CageId/}','{tpl:$cage_data.CageCode/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}