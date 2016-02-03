{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_partnerapp').click(function(){
		addServerBox = divBox.showBox('{tpl:$this.sign/}&ac=add&AppId={tpl:$AppId/}', {title:'添加游戏运营',width:600,height:800});
	});
});


function partnerModify(mid,p_id){
	modifyPartnerBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&PartnerId=' + mid + '&AppId=' + p_id, {title:'修改游戏运营',width:600,height:800});
}
function promptDelete(m_id,p_id){
	deletePartnerBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&PartnerId=' + m_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_partnerapp">添加运营游戏</a> ]
</fieldset>

<fieldset><legend>运营游戏列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="AppId" size="1">
<option value="0">全部</option>
{tpl:loop $appArr $app}
<option value="{tpl:$app.AppId/}" {tpl:if($app.AppId==$AppId)}selected="selected"{/tpl:if}>{tpl:$app.name/}</option>
{/tpl:loop}
</select>
<input type="submit" name="Submit" value="查询" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
	<th align="center" class="rowtip">合作商名称</th>
	<th align="center" class="rowtip">产品名称</th>
  	<th align="center" class="rowtip">需要激活</th>			  
	<th align="center" class="rowtip">收入分成比例</th>
	<th align="center" class="rowtip">官网地址</th>
	<th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $oPartnerAppArr $partner}
  <tr class="hover">
    <td><a href="{tpl:$this.sign/}&PartnerId={tpl:$partner.PartnerId/}">{tpl:$partner.name/}</a></td>
    <td><a href="{tpl:$this.sign/}&AppId={tpl:$partner.AppId/}">{tpl:$partner.product_name/}</a></td>
  	<td>{tpl:if ($partner.IsActive==0)}否{tpl:else}是{/tpl:if}</td>

    <td>{tpl:$partner.income_rate/}</td>
    <td>{tpl:$partner.game_site/}</td>
    <td align="center"><a  href="javascript:;" onclick="promptDelete('{tpl:$partner.PartnerId/}','{tpl:$partner.AppId/}')">删除</a> | <a href="javascript:;" onclick="partnerModify('{tpl:$partner.PartnerId/}','{tpl:$partner.AppId/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}