{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="area_modify_form" id="area_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<fieldset><legend>添加地区</legend>

		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">


<input type="hidden" name="AreaId" id="AreaId" class="span4" value="{tpl:$area.AreaId/}"/>
<td>地区ID</td>
<td>{tpl:$area.AreaId/}</td>
</tr>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$area.name/}"/></td>
</tr>

<tr>
<td>汇率</td>
<td><input type="text" name="currency_rate" id="currency_rate" class="span4"  size="50" value="{tpl:$area.currency_rate/}"/> </td>
</tr>

<tr>
<td>国内/国外</td>
<td>
<input type="radio" name="is_abroad" value="1" {tpl:if($area.is_abroad==1)}checked{/tpl:if}>国内
<input type="radio" name="is_abroad" value="2" {tpl:if($area.is_abroad==2)}checked{/tpl:if}>国外
</td>
</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="area_modify_submit">提交</button></td>
		<td></td>
		</tr>
</table>
	</fieldset>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#area_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '名称不能为空，请修正后再次提交';
					errors[9] = '修改地区失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改地区成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#area_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}