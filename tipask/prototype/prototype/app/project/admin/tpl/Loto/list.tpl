{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_loto').click(function(){
		addLotoBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加抽奖', width:600, height:480});
	});
});
function lotoModify(mid){
	modifyLotoBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&LotoId=' + mid, {title:'修改抽奖', width:600, height:500});
}
function promptDelete(p_id,p_name){
	deleteLotoBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&LotoId=' + p_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_loto">添加抽奖</a> ]
</fieldset>
<fieldset><legend>抽奖列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">

				</form>
<tr><th align="center" class="rowtip">抽奖ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">开始时间</th>
<th align="center" class="rowtip">结束时间</th>
<th align="center" class="rowtip">每人限制抽奖次数</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $LotoArr $Loto $loto_data}
<tr>
<td>{tpl:$loto_data.LotoId/}</td>
<td>{tpl:$loto_data.LotoName/}</td>
<td>{tpl:$loto_data.StartTime func="date('Y-m-d H:i:s',@@)"/}</td>
<td>{tpl:$loto_data.EndTime func="date('Y-m-d H:i:s',@@)"/}</td>
<td>{tpl:$loto_data.UserLotoLimit /}</td>
<td><a href="javascript:;" onclick="lotoModify('{tpl:$loto_data.LotoId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$loto_data.LotoId/}','{tpl:$loto_data.LotoName/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>
</dd>
</dl>
{tpl:tpl contentFooter/}