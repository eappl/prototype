{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script type="text/javascript">
function obj_onchange_partner()
{
	app=$("#AppId");
	partner=$("#PartnerId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/server&ac=server.by.app.partner&AppId="+$("#AppId").val()+"&PartnerId="+$("#PartnerId").val(),
		
		success: function(msg)
		{
			$("#ServerId").html(msg);
		}
	});
	//*/
}function obj_onchange_app()
{
	app=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/server&ac=partner.by.app&AppId="+$("#AppId").val(),
		
		success: function(msg)
		{
			$("#PartnerId").html(msg);
		}
	});
	//*/
}
</script>

<form name="queue_add_form" id="queue_add_form" action="{tpl:$this.sign/}&ac=insert.queue" method="post">
	<table class="table table-bordered table-striped" width="100%">

				<tr>
			<th><label for="AppId">游戏</label></th>
			<td><select name="AppId" id="AppId" onchange="obj_onchange_app()">
				{tpl:loop $appArr $app}
				<option value="{tpl:$app.AppId/}" {tpl:if($app.AppId == $AppId)}selected {/tpl:if}>{tpl:$app.name/}</option>
				{/tpl:loop}
			</select></td>
		</tr>
		<tr>
			<th><label for="PartnerId">平台</label></th>
			<td><select name="PartnerId" id="PartnerId"  onchange="obj_onchange_partner(app.value,this.value, 'server')">
				{tpl:loop $partnerArr $partner}
				<option value="{tpl:$partner.PartnerId/}" {tpl:if($partner.PartnerId==$PartnerId)}selected="selected"{/tpl:if}>{tpl:$partner.name/}</option>
				{/tpl:loop}
			</select> </td>
		</tr>
		<tr>
			<th><label for="ServerId">服务器</label></th>
			<td><select name="ServerId" id="ServerId">				
			</select> </td>
		</tr>
		<tr>
			<th><label for="StartTime">开始发送时间</label></th>
			<td>
				<input type="text" name="StartTime"  class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" />
			</td>
		</tr>
		<tr>
			<th><label >间隔（秒）</label></th>
			<td>
			<input type="text" name="Lag" id="Lag">
			</td>
		</tr>
		<tr>
			<th><label >发送次数</label></th>
			<td>
			<input type="text" name="Count" id="Count" value = 1>

			</td>
		</tr>
		<tr>
			<th><label >倒记数（秒）</label></th>
			<td>
			<input type="text" name="CountDown" id="CountDown">

			</td>
		</tr>
		<tr>
		<th><label for="MailContent">信息内容</label></th><td>
		<textarea name="MessegeContent" id="MessegeContent" cols="45" rows="5"></textarea>
		</td>
		</tr>
		<tr class="noborder">
			<th></th>
			<td>
            <input type="hidden" name="uType" value="{tpl:$uType/}" />
			<button type="submit" id="queue_add_submit">提交</button>
			</td>
		</tr>
	</table>
	</form>
<script type="text/javascript">
document.getElementById('MessegeContent').focus();
$(function(){
	$('#queue_add_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '开始发送时间过早';
					errors[2] = '发送次数不能为0或负数';
					errors[3] = '倒记数时间不能为0或负数';
					errors[4] = '信息内容不能为空';
					errors[5] = '间隔时间不能为0或负数';
					errors[9] = '添加区服失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '成功加入' + jsonResponse.success+'条队列';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#queue_add_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}