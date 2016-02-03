{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#gen_pack_code').click(function(){
		genPackCodeBox = divBox.showBox('{tpl:$this.sign/}&ac=gen', {title:'生成礼包码', width:600, height:450});
	});
});
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

<form name="gen_pack_code_form" id="gen_pack_code_form" action="{tpl:$this.sign/}&ac=gen.pack.code" method="post">
<input type = 'hidden' name = "app_type" id = "app_type" value = 0>
<input type = 'hidden' name = "partner_type" id = "partner_type" value = 0>
<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = 0>
<input type = 'hidden' name = "AreaId" id = "AreaId" value = 0>
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
		<tr class="hover">
			<td>选择游戏:</td>
			<td align="left">        
			<select name = "AppId" id = "AppId" class="span2" onclick="getpermittedweeparter();getproductpack()">
        	{tpl:loop $permitted_app $key $app}
            <option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
            {/tpl:loop}
        </select>
        </td>
		</tr>
        
		<tr class="hover">
			<td>选择平台:</td>
			<td align="left">        
	        <select name = "PartnerId" id = "PartnerId" class="span2">
	        	<option value = '0' {tpl:if (0==$PartnerId)}selected{/tpl:if}> 全部 </option>
	        	 {tpl:loop $permitted_partner $partner_key $partner}
	        			<option value = {tpl:$partner_key/} {tpl:if ($partner_key==$PartnerId)}selected{/tpl:if}>{tpl:$partner.name/}</option>
	        	 {/tpl:loop}
	        </select>
        </td>
		</tr>

		<tr class="hover">
			<td>选择产品类型包：</td>
			<td align="left">        
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
        </td>
		</tr>
		<tr class="hover">
			<td>是否需要绑定用户:</td>
			<td align="left">        
	        <select name = "needBind" id = "needBind" class="span2">
	        	<option value = '2' > 不需要 </option>
	        	<option value = '1' > 需要 </option>

	        </select>
        </td>
		</tr>
		<tr class="hover">
			<td>生成数量:</td>
			<td align="left">        
    		<input type="text" name="GenNum" value="{tpl:$GenNum /}" class='span1'  />

        </td>
		</tr>
		
		<tr class="hover">
			<td>失效日期：</td>
			<td align="left">        
			<input type="text" name="EndTime" value="{tpl:$EndTime/}" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
        </td>
		</tr>		
		
		<tr class="hover">
			<td></td>
			<td align="left">        
   			<input type = 'submit' id="gen_pack_code_submit" class="btn btn-info btn-small" value = '生成'>

        </td>
		</tr>
</form>

<script type="text/javascript">
$('#gen_pack_code_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
			
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
					errors[1] = '必须选定一个游戏，请修正后再次提交';
					errors[2] = '必须选定一个平台，请修正后再次提交';
					errors[3] = '必须选定一个产品包，请修正后再次提交';
					errors[4] = '游戏-平台配置错误，请修正后再次提交';
					errors[5] = '数量不得为小于零的证书，请修正后再次提交';
					errors[9] = '生成失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '成功生成'+jsonResponse.Gened+'条 礼包码';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml( jsonResponse.sign + '&AppId=' + jsonResponse.AppId+ '&PartnerId=' + jsonResponse.PartnerId + '&ProductPackId=' + jsonResponse.ProductPackId);}});

			}
		}
	};
	$('#gen_pack_code_form').ajaxForm(options);
});
</script>

</dd>
</dl>
{tpl:tpl contentFooter/}