{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="type_add_form" id="type_add_form" action="{tpl:$this.sign/}&ac=insert.type" method="post">
	<table class="table table-bordered table-striped" width="100%">
		<tr>
			<th>Socket类型</th>
			<td><input type="text" name="Type" /> </td>
		</tr>
        <tr>
			<th>名称</th>
			<td><input type="text" name="Name" /> </td>
		</tr>
        <tr>
			<th>压包字串</th>
			<td><input type="text" name="PackFormat" /> </td>
		</tr>
        <tr>
			<th>解包字串</th>
			<td><input type="text" name="UnPackFormat" /> </td>
		</tr>
        <tr>
			<th>长度</th>
			<td><input type="text" name="Length" /> </td>
		</tr>
		<tr class="noborder">
			<th></th>
			<td>
			<button type="submit" id="type_add_submit">提交</button>
			</td>
		</tr>
	</table>
	</form>
<script type="text/javascript">
$(function(){
	$('#type_add_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = 'socket类型不能为空';
                    errors[2] = '长度不能为空';
                    errors[3] = 'socket不是数字';
                    errors[4] = '长度不是数字';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
					} else {
				var message = '添加队列类型成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}&ac=index.type');}});
				}
			}
		};
		$('#type_add_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}