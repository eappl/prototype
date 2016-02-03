{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#gen_pack_code').click(function(){
		genPackCodeBox = divBox.showBox('{tpl:$this.sign/}&ac=gen', {title:'生成礼包码', width:600, height:400});
	});
});
function asignProductPackCode(m_id){
	asignProductPackCodeBox = divBox.showBox('{tpl:$this.sign/}&ac=asign.product.pack.code&GenId=' + m_id, {title:'分配礼包码', width:600, height:500});
}
function getpermittedweeapp()
{
	app_type=$("#app_type");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.app&app_type="+app_type.val(),
		
		success: function(msg)
		{
			$("#AppId").html(msg);
		}
	});
	//*/
}
function getpermittedweeparter()
{
	partner_type=$("#partner_type");
	AppId=$("#AppId");
	is_abroad=$("#is_abroad");
	AreaId=$("#AreaId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.partner&AppId="+AppId.val()+"&partner_type="+partner_type.val()+"&is_abroad="+is_abroad.val()+"&AreaId="+AreaId.val(),
		success: function(msg)
		{
			$("#PartnerId").html(msg);
		}
	});
	//*/
}
function getproductpack()
{
	app=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/product/pack&ac=get.product.pack&AppId="+app.val(),
		
		success: function(msg)
		{
			$("#ProductPackId").html(msg);
		}
	});
	//*/
}

</script>

<form name="selection" id="selection" action="{tpl:$page_form_action /}" method="post">
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="gen_pack_code">生成产品包</a> ]
</fieldset>
<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
<input type = 'hidden' name = "page" id = "page" value = "{tpl:$page /}">
<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
        选择游戏:
        <select name = "AppId" id = "AppId" class="span2" onchange="getpermittedweeparter();getproductpack()">
        	{tpl:loop $permitted_app $key $app}
            <option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
            {/tpl:loop}
        </select>
        选择平台:
        <select name = "PartnerId" id = "PartnerId" class="span2">
        	<option value = '0' {tpl:if (0==$PartnerId)}selected{/tpl:if}> 全部 </option>
        	 {tpl:loop $permitted_partner $partner_key $partner}
        			<option value = {tpl:$partner_key/} {tpl:if ($partner_key==$PartnerId)}selected{/tpl:if}>{tpl:$partner.name/}</option>
        	 {/tpl:loop}
        </select>
		选择产品类型包：
			<select name = "ProductPackId" id = "ProductPackId">
			<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
			{tpl:loop $ProductPackArr $App $AppInfo}
			{tpl:if ($App==$AppId)}
			{tpl:loop $AppInfo $key $pack}
						<option value = {tpl:$key/} {tpl:if ($key==$ProductPackId)}selected{/tpl:if}>{tpl:$pack.name/}</option>

			{/tpl:loop}
			{/tpl:if}
			{/tpl:loop}
			</select>
   <input type = 'submit' class="btn btn-info btn-small" value = '查询'>


</fieldset>
</form>

<fieldset><legend>{tpl:$page_title /}</legend>
<table class="table table-bordered table-striped">
<tr><th style="text-align:center">记录ID</th><th style="text-align:center">礼包名称</th><th style="text-align:center">礼包内容</th><th style="text-align:center">游戏</th><th style="text-align:center">大区</th><th style="text-align:center">生成时间</th><th style="text-align:center">失效时间</th><th style="text-align:center">生成管理员</th><th style="text-align:center">是否需要绑定用户</th><th style="text-align:center">预计生成条数</th><th style="text-align:center">实际生成条数</th><th style="text-align:center">操作</th></tr>

{tpl:loop $GenLog.GenLog $GenId $GenInfo}
<tr>
<td style="text-align:center">{tpl:$GenInfo.GenId/}</td>
<td style="text-align:center">{tpl:$GenInfo.PackName/}</td>
<td style="text-align:center">{tpl:$GenInfo.ProductListText/}</td>
<td style="text-align:center">{tpl:$GenInfo.AppName/}</td>
<td style="text-align:center">{tpl:$GenInfo.PartnerName/}</td>
<td style="text-align:center">{tpl:$GenInfo.GenTime func="date('Y-m-d H:i:s',@@)"/}</td>
<td style="text-align:center">{tpl:$GenInfo.EndTime func="date('Y-m-d H:i:s',@@)"/}</td>
<td style="text-align:center">{tpl:$GenInfo.ManagerName/}</td>
<td style="text-align:center">{tpl:if ($GenInfo.needBind==1)}是{tpl:else}否{/tpl:if}</td>
<td style="text-align:center">{tpl:$GenInfo.GenNum func="number_format(sprintf('%10d',@@),0)"/}</td>
<td style="text-align:center">{tpl:$GenInfo.GenedNum func="number_format(sprintf('%10d',@@),0)"/}</td>
<td style="text-align:center">{tpl:$GenInfo.ExportUrl /}{tpl:if ($GenInfo.needBind==1)}<a href="javascript:;" onclick="asignProductPackCode('{tpl:$GenInfo.GenId/}');">|<分配礼包码></a>{tpl:else}{/tpl:if}</td>
</tr>
{/tpl:loop}
</table>
{tpl:$page_content/}
</fieldset>

</dd>
</dl>
{tpl:tpl contentFooter/}