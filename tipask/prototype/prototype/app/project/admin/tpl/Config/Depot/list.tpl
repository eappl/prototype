{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_depot').click(function(){
		addDepotBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加机房',width:550,height:260});
	});
});
function depotModify(mid){
	modifyDepotBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&DepotId=' + mid, {title:'修改机房', width:550, height:300});
}

function promptDelete(m_id,depotName){
	
	$.ajax
	({
		type:"GET",
		url:"?ctl=config/depot&ac=get.depot.delmes&DepotId="+m_id,
		success:function(data)
		{	
			if(data==1)
			{
				divBox.alertBox("此机房下有机柜，不能删除！");
			}else{						
		      deleteDepotBox = divBox.confirmBox({content:'是否删除 ' + depotName + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&DepotId=' + m_id;}});
			}
		}
	})
	
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_depot">添加机房</a> ]
</fieldset>
<fieldset><legend>机房列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
</form>
<tr><th align="center" class="rowtip">机房ID</th>
<th align="center" class="rowtip">机房名称</th>
<th align="center" class="rowtip">机房排数编号</th>
<th align="center" class="rowtip">机柜数量</th>
<th align="center" class="rowtip">最新更新时间</th>
<th align="center" class="rowtip">备注</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $DepotArr $Depot $depot_data}
<tr>
<td>{tpl:$depot_data.DepotId/}</td>
<td>{tpl:$depot_data.name/}</td>
<td>{tpl:$depot_data.X/}</td>
<td>{tpl:$depot_data.count/}</td>
<td>{tpl:$depot_data.Udate/}</td>
<td>{tpl:$depot_data.Comment/}</td>
<td><a href="javascript:;" onclick="depotModify('{tpl:$depot_data.DepotId/}');">修改</a> 
| <a href="{tpl:$this.sign/}&ac=machine.map&DepotId={tpl:$depot_data.DepotId/}&X={tpl:$depot_data.FirstX/}">机器分布图</a> 
| <a href="javascript:;" onclick="promptDelete('{tpl:$depot_data.DepotId/}','{tpl:$depot_data.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}