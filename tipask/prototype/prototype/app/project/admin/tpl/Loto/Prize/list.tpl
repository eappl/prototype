{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_prize').click(function(){
		addPrizeBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加奖品',width:600, height:200});
	});
});
function jobModify(mid){
	modifyPrizeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&LotoPrizeId=' + mid, {title:'修改奖品', contentType:'ajax', width:600, height:250, showOk:false, showCancel:false});
}

function promptDelete(p_id,p_name){
	deletePrizeBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&PrizeId=' + p_id;}});

}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_prize">添加奖品</a> ]
</fieldset>
<fieldset><legend>奖品列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
选择抽奖
			<select name = "LotoId" id = "LotoId">
			<option value = 0 {tpl:if (0==$LotoId)}selected{/tpl:if}> 全部 </option>
			{tpl:loop $LotoList $key $loto}
			<option value = {tpl:$key/} {tpl:if ($key==$LotoId)}selected{/tpl:if}>{tpl:$loto.LotoName/}</option>
			{/tpl:loop}
			</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">
<tr><th align="center" class="rowtip">奖品ID</th>
<th align="center" class="rowtip">奖品名称</th>
<th align="center" class="rowtip">所属抽奖</th>
<th align="center" class="rowtip">总数</th>
<th align="center" class="rowtip">已抽中</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $PrizeArr $Loto $loto_data}
	{tpl:loop $loto_data $LotoPrize $prize_data}
<tr>
<td>{tpl:$prize_data.LotoPrizeId/}</td>
<td>{tpl:$prize_data.LotoPrizeName/}</td>
<td>{tpl:$prize_data.LotoName/}</td>
<td>{tpl:$prize_data.LotoPrizeCount/}</td>
<td>{tpl:$prize_data.LotoPrizeCountUsed/}</td>
<td><a href="javascript:;" onclick="jobModify('{tpl:$prize_data.LotoPrizeId/}');">修改</a>
|<a href="{tpl:$this.sign/}&ac=detail&LotoPrizeId={tpl:$prize_data.LotoPrizeId/}">详细配置配置</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$prize_data.LotoPrizeId/}','{tpl:$prize_data.LotoPrizeName/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

</dd>
</dl>
{tpl:tpl contentFooter/}