{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="server_modify_form" id="server_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
<table class="table table-bordered table-striped" width="100%">
<input type="hidden" name="old_ServerId" value="{tpl:$server.ServerId/}" />

<tr><th><label for="ServerId">服务器ID</label></th><td>
<input type="text" name="ServerId" id="ServerId" class="span4" value="{tpl:$server.ServerId/}"/></td></tr>

<tr>
<th><label for="name">服务器名称</label></th><td>
<input type="text" name="name" id="name" class="span4" value="{tpl:$server.name/}"/></td></tr>

<tr>
	<th><label for="AppId">游戏</label></th><td>
	<select name="AppId" id="AppId" onchange="obj_onchange(this.value,'par_id')">
	{tpl:loop $appArr $app}<option value="{tpl:$app.AppId/}" {tpl:if($server.AppId == $app.AppId)}selected {/tpl:if}>{tpl:$app.name/}</option>{/tpl:loop}
	</select></td>
</tr>
<tr>
	<th><label for="PartnerId">平台</label></th><td>
	<select name="PartnerId" id="par_id">
	{tpl:loop $partnerArr $partner}<option value="{tpl:$partner.PartnerId/}" {tpl:if($server.PartnerId == $partner.PartnerId)}selected {/tpl:if}>{tpl:$partner.name/}</option>{/tpl:loop}
	</select></td>
</tr>


		<tr>
			<th><label for="LoginStart">开服时间</label></th>
			<td>
				<input type="text" name="LoginStart" value="{tpl:$server.LoginStart/}" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" />
			</td>
		</tr>
		<tr>
			<th><label >开停结止时间</label></th>
			<td>
			<input type="text" name="NextEnd" value="{tpl:$server.NextEnd/}" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="NextStart" value="{tpl:$server.NextStart/}" value="" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
			</td>
		</tr>
			<th><label >充值结止时间</label></th>
			<td>
			<input type="text" name="PayEnd" value="{tpl:$server.PayEnd/}" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="PayStart" value="{tpl:$server.PayStart/}" value="" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
			</td>
		</tr>
<tr>
<th><label for="ServerIp">游戏服务器IP</label></th><td>
<input type="text" name="ServerIp" id="ServerIp" class="span4" value="{tpl:$server.ServerIp/}"/></td>
</tr>
<tr>
<th><label for="SocketPort">Socket端口</label></th><td>
<input type="text" name="SocketPort" id="SocketPort" class="span4" value="{tpl:$server.SocketPort/}"/></td>
</tr>

<tr>
<th><label for="SocketPort">服务器Socket端口</label></th><td>
<input type="text" name="ServerSocketPort" id="ServerSocketPort" class="span4" value="{tpl:$server.ServerSocketPort/}"/></td>
</tr>
<tr>
<th><label for="GMIp">GM服务器IP</label></th><td>
<input type="text" name="GMIp" id="GMIp" class="span4" value="{tpl:$server.GMIp/}"/></td>
</tr>
<tr>
<th><label for="GMSocketPort">GM服务器Socket端口</label></th><td>
<input type="text" name="GMSocketPort" id="GMSocketPort" class="span4" value="{tpl:$server.GMSocketPort/}"/></td>
</tr>
<th><label for="is_show">是否对外显示</label></th><td>
	   <select name="is_show">
	   <option value="1"  {tpl:if($server.is_show==1)}selected="selected"{/tpl:if}>显示</option>
	   <option value="0" {tpl:if($server.is_show==0)}selected="selected"{/tpl:if}>不显示</option>
       </select>  
</td>
</tr>
<tr>
<th><label for="IpListWhite">IP白名单</label></th><td>
<textarea name="IpListWhite" id="IpListWhite">
<?php 
        if(!empty($server['Comment']))
        {
            $arr = json_decode($server['Comment'],true);
                unset($t);
                unset($key);
            if(isset($arr['IpListWhite']))
            {

                $t = array();
                ksort($arr['IpListWhite']);
                foreach($arr['IpListWhite'] as $key => $value)
                {
                	$t[] = long2ip($key);
               	}
            }
        }
        echo implode(',',$t);
?>
</textarea></td></tr>
<tr>
<th><label for="IpListBlack">IP黑名单</label></th><td>
<textarea name="IpListBlack" id="IpListBlack">
<?php 
        if(!empty($server['Comment']))
        {
            $arr = json_decode($server['Comment'],true);
                unset($t);
                unset($key);
            if(isset($arr['IpListBlack']))
            {

                $t = array();
                ksort($arr['IpListBlack']);
                foreach($arr['IpListBlack'] as $key => $value)
                {
                	$t[] = long2ip($key);
               	}
            }
        }
        echo implode(',',$t);
?>
</textarea></td>
</tr>

<tr class="noborder"><th></th><td>
<button type="submit" id="server_modify_submit">提交</button></td></tr>
</table>
</form>

<script type="text/javascript">
$('#server_modify_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
			
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
					errors[2] = '名称不能为空，请修正后再次提交';
					errors[3] = '停服时间不正确，请修正后再次提交';
					errors[4] = '充值时间不正确，请修正后再次提交';
					errors[9] = '修改服务器失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改服务器成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.app+ '&PartnerId=' + jsonResponse.partner);}});

			}
		}
	};
	$('#server_modify_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}