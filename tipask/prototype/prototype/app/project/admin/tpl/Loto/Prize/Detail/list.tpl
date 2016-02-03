{tpl:tpl contentHeader/}

<script type="text/javascript">
$(document).ready(function(){
	$('#add_prize_detail').click(function(){
		adPrizeDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=add.detail&LotoPrizeId='+{tpl:$LotoPrizeInfo.LotoPrizeId/}, {title:'添加概率', width:600, height:300});
	});
});
function lotoModify(mid){
	modifyPrizeDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=modify.detail&LotoPrizeDetailId=' + mid, {title:'修改概率', width:600, height:300});
}

function promptDelete(p_id){
	deletePrizeDetailBox = divBox.confirmBox({content:'是否删除 ' + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete.detail&LotoPrizeDetailId=' + p_id;}});

}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_prize_detail">添加概率</a> ]

</fieldset>
<fieldset><legend>{tpl:$LotoPrizeInfo.LotoPrizeName/} 概率详情列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">

				</form>
<tr><th align="center" class="rowtip">开始时间</th>
<th align="center" class="rowtip">结束时间</th>
<th align="center" class="rowtip">概率</th><th align="center" class="rowtip">奖品数量</th>
<th align="center" class="rowtip">已抽中奖品数量</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $PrizeDetailList $key $detail_data}
<tr>
<td>{tpl:$detail_data.StartTime func="date('Y-m-d H:i:s',@@)"/}</td>
<td>{tpl:$detail_data.EndTime func="date('Y-m-d H:i:s',@@)"/}</td>
<td>{tpl:$detail_data.PrizeRate/} /10000</td>
<td>{tpl:$detail_data.LotoPrizeCount func="number_format(sprintf('%10d',@@),0)"/}</td>
<td>{tpl:$detail_data.LotoPrizeCountUsed func="number_format(sprintf('%10d',@@),0)"/}</td>
<td><a href="javascript:;" onclick="lotoModify('{tpl:$detail_data.LotoPrizeDetailId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$detail_data.LotoPrizeDetailId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

</dd>
</dl>
{tpl:tpl contentFooter/}