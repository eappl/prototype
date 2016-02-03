{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="type_modify_form" id="type_modify_form" action="{tpl:$this.sign/}&ac=update.type" method="post">
	<table class="table table-bordered table-striped" width="100%">
		<tr>
			<th>Socket类型</th>
			<td>{tpl:$SocketType.Type/}</td>
		</tr>
        <tr>
			<th>名称</th>
			<td><input type="text" name="Name" value="{tpl:$SocketType.Name/}" /> </td>
		</tr>
        <tr>
			<th>压包字串</th>
			<td><input type="text" name="PackFormat" value="{tpl:$SocketType.PackFormat/}" /> </td>
		</tr>
        <tr>
			<th>解包字串</th>
			<td><input type="text" name="UnPackFormat" value="{tpl:$SocketType.UnPackFormat/}" /> </td>
		</tr>
        <tr>
			<th>长度</th>
			<td><input type="text" name="Length" value="{tpl:$SocketType.Length/}" /> </td>
		</tr>
		<tr class="noborder">
			<th></th>
			<td>
            <input type="hidden" name="Type" value="{tpl:$SocketType.Type/}" />
			<button type="submit" id="type_modify_submit">提交</button>
			</td>
		</tr>
	</table>
	</form>
<script type="text/javascript">
$(function(){
	$('#type_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = 'socket类型不能为空';
                    errors[2] = '长度不能为空';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改队列类型成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}&ac=index.type');}});
				}
			}
		};
		$('#type_modify_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}