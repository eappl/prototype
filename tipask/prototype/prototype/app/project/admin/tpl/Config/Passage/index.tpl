{tpl:tpl contentHeader/}
<script type="text/javascript">
function promptDelete(p_id, p_name){
	deletePassageBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&passage_id=' + p_id;}});
}

$(document).ready(function(){
	$('#add_passage').click(function(){
		addPassageBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加支付平台',width:500,height:600});
	});
});

function passageModify(p_id){
	modifyPassageBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&passage_id=' + p_id, {title:'修改支付平台',width:500,height:550});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_passage">添加支付平台</a> ]
</fieldset>

<form id="list_form" name="list_form" action="?ctl=passge" method="post">
<fieldset><legend>支付平台列表</legend>
<table class="table table-bordered table-striped" width="100%">
<tr><th align="center" class="rowtip">支付平台ID</th>
<th align="center" class="rowtip">标识</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">比率</th>
<th align="center" class="rowtip">账务比率</th>
<th align="center" class="rowtip">分类</th>
<th align="center" class="rowtip">支付平台的本方账号</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $passageArr $passage}
<tr class="hover">
<td>{tpl:$passage.passage_id/}</td>
<td>{tpl:$passage.passage/}</td>
<td>{tpl:$passage.name/}</td>
<td>{tpl:$passage.passage_rate func="sprintf('%3.2f',@@*100)"/}%</td>
<td>{tpl:$passage.finance_rate/}%</td>
<td>{tpl:$passage.kindname/}</td>
<td>{tpl:$passage.StagePartnerId/}</td>

<td>  <a href="javascript:;" onclick="passageModify({tpl:$passage.passage_id/});">修改</a>
| <a href="javascript:;" onclick="promptDelete('{tpl:$passage.passage_id/}','{tpl:$passage.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>
</form>
{tpl:tpl contentFooter/}