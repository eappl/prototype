{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="app_add_form" name="app_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>游戏名称</td>
<td align="left"><input name="name" type="text" class="span4" id="name" value="" size="50" /></td>
</tr>
<tr class="hover"><td>游戏ID</td>
<td align="left"><input type="text" class="span4" name="AppId" value="" size="50" id="AppId" /></td>
</tr>
<tr class="hover">
<td>标识</td>
<td align="left" class="rowform"><input type="text" class="span4" name="app_sign" /></td>
</tr>

<tr class="hover">
<td>游戏分类</td>
<td align="left">
<select name="ClassId" id="ClassId">
<option value="0">无分类</option>
{tpl:loop $oGameClaArr $class}
<option value="{tpl:$class.ClassId/}">{tpl:$class.name/}</option>
{/tpl:loop}
</select>
</td>
</tr>

<tr class="hover"><td>游戏描述</td>
<td align="left"><input type="text" class="span4" name="app_desc" value="" size="50" /></td>
</tr>


<tr class="hover"><td>官网</td>
<td align="left"><input type="text" class="span4" name="site_url" value="" size="50" /></td>
</tr>


<tr class="hover"><td>兑换比例</td>
<td align="left"><input type="text" class="span4" name="exchange_rate" value="10" size="50" /></td>
</tr>

<tr class="hover"><td>游戏币名称</td>
<td align="left"><input type="text" class="span4" name="comment[coin_name]" value="金币" size="50" /></td>
</tr>

<tr class="hover"><td>是否对外显示</td>
<td align="left">
	   <select name="is_show">
	   <option value="1">显示</option>
	   <option value="0">不显示</option>
       </select>  
</td>
</tr>

<tr class="hover"><td>是否由平台生成登录记录</td>
<td align="left">
	   <select name="comment[create_loginid]">
	   <option value="1">平台生成</option>
	   <option value="0">游戏生成</option>
       </select>  
</td>
</tr>

<tr class="noborder"><td></td>
<td><button type="submit" id="app_add_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#app_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '产品ID不能为空，请修正后再次提交';
				errors[2] = '产品名称不能为空，请修正后再次提交';
				errors[3] = '产品标识不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加游戏成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#app_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}