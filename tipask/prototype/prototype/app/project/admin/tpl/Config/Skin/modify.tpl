{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="skin_modify_form" id="skin_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>皮肤ID</td>
<td>{tpl:$Skin.SkinId/}</td>
</tr>

		<input type="hidden" name="SkinId" id="SkinId" class="span4" value="{tpl:$Skin.SkinId/}"/>
		<input type="hidden" name="oldAppId" id="oldAppId" class="span4" value="{tpl:$Skin.AppId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$Skin.name/}"/></td>
</tr>

		<tr class="hover">
			<td>选择游戏</td>
			<td align="left">
			<select name = "AppId" id = "AppId" onclick="getherobyapp()">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$Skin.AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>
				<tr class="hover">
			<td>选择英雄</td>
			<td align="left">
<select name = "HeroId" id = "HeroId" >
	<option value = 0 {tpl:if (0==$HeroId)}selected{/tpl:if}> 全部 </option>
	 {tpl:loop $HeroArr $app $appdata}
	 		{tpl:if ($app==$AppId)}
	 		 {tpl:loop $appdata $hero $herodata}
			<option value = {tpl:$hero/} {tpl:if ($hero==$Skin.HeroId)}selected{/tpl:if}>{tpl:$herodata.name/}</option>
			{/tpl:loop}
			{/tpl:if}
	 {/tpl:loop}
</select>
</td>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="skin_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#skin_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个游戏';
					errors[2] = '失败，必须输入皮肤名称';
					errors[3] = '失败，必须输入皮肤ID';
					errors[4] = '失败，必须选定一个英雄';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改皮肤成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId);}});
				}
			}
		};
		$('#skin_modify_form').ajaxForm(options);
	});
});
function getherobyapp()
{
	App=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/hero&ac=get.hero&AppId="+App.val(),		
		success: function(msg)
		{
			$("#HeroId").html(msg);
		}
	});
	//*/
}
</script>
{tpl:tpl contentFooter/}
