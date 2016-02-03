<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form name="loto_modify_form" id="loto_modify_form" action="<?php echo $this->sign; ?>&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>抽奖ID</td>
<td><?php echo $Loto['LotoId']; ?></td>
</tr>

		<input type="hidden" name="LotoId" id="LotoId" class="span4" value="<?php echo $Loto['LotoId']; ?>"/>
</tr>
<td>名称</td>
<td><input type="text" name="LotoName" id="LotoName" class="span4"   size="50" value="<?php echo $Loto['LotoName']; ?>"/></td>
</tr>	
		<tr class="hover">
			<td>起始时间</td>
			<td align="left"><input type="text" name="StartTime" value="<?php echo $Loto['StartTime']; ?>" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="EndTime"  class="input-medium" value="<?php echo $Loto['EndTime']; ?>"		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></td>
		</tr>
</tr>
<tr>
<td>每人限制抽奖次数</td>
<td><input type="text" name="UserLotoLimit" id="UserLotoLimit" class="span4"   size="5" value="<?php echo $Loto['UserLotoLimit']; ?>"/></td>
</tr>	
<tr>
<td>备注</td>
<td>
<textarea rows="5" cols="40" name="Comment" id="Comment"><?php echo $Loto['Comment']; ?></textarea></td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="loto_modify_submit">提交</button></td>
		</tr>
</table>
</form>
</dd>
</dl>
<script type="text/javascript">
$(function(){
	$('#loto_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须输入一个有效的结束时间';
					errors[2] = '失败，必须输入一个有效的开始时间';
					errors[3] = '失败，必须输入抽奖名称';
					errors[4] = '失败，必须指定抽奖ID';
					errors[5] = '失败，必须指定有效的抽奖次数限定';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改抽奖成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
				}
			}
		};
		$('#loto_modify_form').ajaxForm(options);
	});
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>