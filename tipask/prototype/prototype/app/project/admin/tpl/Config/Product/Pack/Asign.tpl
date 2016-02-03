{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script type="text/javascript">
function getproducttype()
{
	app=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/product/type&ac=get.product.type&AppId="+app.val(),
		
		success: function(msg)
		{
			$("#ProductTypeId").html(msg);
		}
	});
	//*/
}
</script>

<form name="asign_code_form" id="asign_code_form" action="{tpl:$this.sign/}&ac=asign.code" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>产品包ID</td>
<td>{tpl:$ProductPackInfo.ProductPackId/}</td>
</tr>
<input type="hidden" name="GenId" id="GenId" value="{tpl:$GenId/}"/>
<tr>
<td>礼包名称</td>
<td>{tpl:$ProductPackInfo.name/}</td>
</tr>
<tr>
<td>游戏</td>
<td align="left">
{tpl:$AppName/}
</td>
</tr>
<tr>
<td>礼包码总数</td>
<td align="left">
{tpl:$GenInfo.CodeCount func="number_format(sprintf('%10d',@@),0)"/}
</td>
</tr>	
<tr>
<td>已分配</td>
<td align="left">
{tpl:$GenInfo.AsingedCodeCount func="number_format(sprintf('%10d',@@),0)"/}
</td>
</tr>
<tr>
<td>已使用</td>
<td align="left">
{tpl:$GenInfo.UsedCodeCount func="number_format(sprintf('%10d',@@),0)"/}
</td>
</tr>			
<tr>
<td>用户列表</td>
<td align="left">
<textarea name="UserName" id="UserName"></textarea>
</td>
</tr>	
			</table>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="asign_code_submit">提交</button></td>
		</tr>
</table>
	</fieldset>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#asign_code_submit').click(function(){		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {
			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];					
                    errors[2] = '必须指定一个礼包码生成批次';
                    errors[3] = '用户名不能为空';
                    errors[4] = '该批次为非绑定礼包码';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '成功分配' + jsonResponse.success+'礼包码';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#asign_code_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}