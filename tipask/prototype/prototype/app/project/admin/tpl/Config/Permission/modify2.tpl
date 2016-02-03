{tpl:tpl contentHeader/}
<form name="group_update_form" id="group_update_form" action="?ctl=config/permission&ac=permission.modify2" method="post" style="width:100%; margin-left: 20px;">
<script type="text/javascript" src="{tpl:$config.js/}jquery.js"></script>
<script type="text/javascript">	
$(function(){
	$(".global,.area,.type").click(function(){
		var val = $(this).val();
		var check = $(this).attr("checked");
		if(check)
		{
			$("input[value*="+val+"]").attr("checked",true);
		}else{
			$("input[value*="+val+"]").attr("checked",false);
		}		
	})
})
</script>
<INPUT TYPE="hidden" NAME="group_id" id="group_id" value="{tpl:$group_id/}">
	<!--针对所有游戏-->
	<fieldset>
	<legend>全局</legend>
	{tpl:loop $totalPermission.total $AreaId $area_data}
	<input type="checkbox"  value="Area|{tpl:$AreaId/}" class="area">{tpl:$area_data.name/}
	(
	{tpl:loop $area_data.partner_type $partner_type $partner_type_data} 
	<input type='checkbox'  value="Area|{tpl:$AreaId/}_Type|{tpl:$partner_type/}" class="type">{tpl:$partner_type_data.name/}
  	{/tpl:loop}
	)
	{/tpl:loop}
	</fieldset>
<br/>
&nbsp;
<br/>
	<!--游戏-->
	{tpl:loop $totalPermission.list $AppId $area_data}<!--循环游戏-->
		{tpl:$area_data.name/}　
		<input type="checkbox"  value="App|{tpl:$AppId/}" class="global">全局
		{tpl:loop $area_data.default $AreaId $area_data}<!--循环区域　比如中国　韩国-->
		<dl style="margin-left: 60px;">
			<dt><input type="checkbox" value="App|{tpl:$AppId/}_Area|{tpl:$AreaId/}" class="area">{tpl:$area_data.name/}</dt>
			<dd>
			{tpl:loop $area_data.partner_type $partner_type $partner_type_data} <!--循环官服和专区-->
			<input type="checkbox" value="App|{tpl:$AppId/}_Area|{tpl:$AreaId/}_Type|{tpl:$partner_type/}" class="type" id="{tpl:$AppId/}_{tpl:$AreaId/}_{tpl:$AreaId/}">{tpl:$partner_type_data.name/}
			(
			        <?php
					$i=0;
					$partnercount = count($partner_type_data['partner']);
				?>     
				{tpl:loop $partner_type_data.partner $PartnerId $partner_data} <!--循环专区-->
				<input type="checkbox" {tpl:if ($partner_data.permission==1)} checked {/tpl:if}
				       name="PartnerIds[]" value="App|{tpl:$AppId/}_Area|{tpl:$AreaId/}_Type|{tpl:$partner_type/}_Partner|{tpl:$partner_data.PartnerId/}">{tpl:$partner_data.name/}
				<?php
				if($partner_data['permission'] == 1){
					$i++;
				}
				?>
				{/tpl:loop}
				{tpl:if ($i==$partnercount)}
				<script>
					document.getElementById("{tpl:$AppId/}_{tpl:$AreaId/}_{tpl:$AreaId/}").checked = true;
				</script>
				{/tpl:if}
			)				
			&nbsp;&nbsp;
			{/tpl:loop}		
			</dd>
		</dl>		
		{/tpl:loop}			
	{/tpl:loop}
	
	<button type="submit" id="group_update_submit" name="submit">提交</button></td><td>&nbsp;</td>
</form>
<!--无尽英雄　　<input type="checkbox" name="App1" class="global">全局
	<dl>		
		<dt><input type="checkbox" name="App1_Area1" class="area">中国</dt>
		<dd><input type="checkbox" name="App1_Area1_Type1" class="type" class="type">官服
		（<input type="checkbox" name="App1_Area1_Type1_Partner1">狸猫）&nbsp;
		<input type="checkbox" name="App1_Area1_Type2" class="type">专区
		（<input type="checkbox" name="App1_Area1_Type2_Partner2">台湾１
		<input type="checkbox" name="App1_Area1_Type2_Partner3">台湾２）
		</dd>

		<dt><input type="checkbox" name="App1_Area2" class="area">韩国</dt>

		<dd><input type="checkbox" name="App1_Area2_Type1" class="type">官服
		（<input type="checkbox" name="App1_Area2_Type1_Partner1">狸猫）&nbsp;
		<input type="checkbox" name="App1_Area2_Type2" class="type">专区
		（<input type="checkbox" name="App1_Area2_Type2_Partner1">韩国１
		<input type="checkbox" name="App1_Area2_Type2_Partner2">韩国２）
		</dd>
	</dl>-->
{tpl:tpl contentFooter/}
