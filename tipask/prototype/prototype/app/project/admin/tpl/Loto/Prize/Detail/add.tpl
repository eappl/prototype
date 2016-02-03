{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="prizedetail_add_form" name="prizedetail_add_form" action="{tpl:$this.sign/}&ac=insert.detail" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr>
<td>概率</td>
<td><input type="text" name="PrizeRate" id="PrizeRate" class="span4" />/10000</td>
</tr>
<tr>
<td>奖品数量</td>
<td><input type="text" name="LotoPrizeCount" id="LotoPrizeCount" class="span4" /></td>
</tr>

		<input type="hidden" name="LotoPrizeId" id="LotoPrizeId" class="span4" value="{tpl:$LotoPrizeId/}"/>
		<tr class="hover">
			<td>起始时间</td>
			<td align="left"><input type="text" name="StartTime"  class="input-meidum"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="EndTime"  class="input-medium" 	onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></td>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="prizedetail_add_submit">提交</button></td>
		</tr>
</table>
	</form>
	</dd>
</dl>
<script type="text/javascript">
$(function(){
	$('#prizedetail_add_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须指定一个有效的概率';
					errors[2] = '失败，必须指定一个有效的结束时间';
					errors[3] = '失败，必须指定一个奖品ID';
					errors[4] = '失败，必须指定一个有效的开始时间';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加概率成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&ac=detail&LotoPrizeId=' + $("#LotoPrizeId").val());}});

				}
			}
		};
		$('#prizedetail_add_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}

