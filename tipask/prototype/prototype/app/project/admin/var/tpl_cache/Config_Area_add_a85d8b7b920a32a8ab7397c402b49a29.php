<?php include Base_Common::tpl('contentHeader'); ?>
<div class="br_bottom"></div>
<form id="area_add_form" name="area_add_form" action="<?php echo $this->sign; ?>&ac=insert" metdod="post">
		<fieldset><legend>添加地区</legend>

		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

		<tr class="hover">
			<td>地区名称</td>
			<td align="left"><input name="name" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>

		<tr class="hover">
			<td>汇率</td>
			<td align="left"><input type="text" name="currency_rate" id="currency_rate" class="span4" size="50" /></td>
		</tr>
	
		<tr class="hover">
			<td>国内/国外</td>
			<td align="left">
				<input type="radio" name="is_abroad" id="is_abroad" value = "1">国内

			<input type="radio" name="is_abroad" id="is_abroad" value = "2">国外
			</td>
		</tr>
		
		<tr class="noborder"><td></td>
		<td><button type="submit" id="area_add_submit">提交</button></td>
		</tr>
	</table>
	</fieldset>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('name').focus();
$('#area_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加地区成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
			}
		}
	};
	$('#area_add_form').ajaxForm(options);
});

</script>
<?php include Base_Common::tpl('contentFooter'); ?>
