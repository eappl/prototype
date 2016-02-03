{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="app_update_form" name="app_update_form" action="{tpl:$this.sign/}&ac=update" metdod="post">
<input type="hidden" name="AppId" value="{tpl:$oAppArr.AppId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>游戏名称</td>
<td align="left"><input name="name" type="text" class="span4" id="name" value="{tpl:$oAppArr.name/}" size="50" /></td>
</tr>
<tr class="hover"><td>游戏ID</td>
<td align="left">{tpl:$oAppArr.AppId/}</td>
</tr>
<tr class="hover">
<td>标识</td>
<td align="left" class="rowform"><input type="text" class="span4" name="app_sign" value="{tpl:$oAppArr.app_sign/}" /></td>
</tr>

<tr class="hover">
<td>游戏分类</td>
<td align="left">
<select name="ClassId" id="ClassId">
<option value="0">无分类</option>
{tpl:loop $oGameClaArr $class}
<option value="{tpl:$class.ClassId/}"  {tpl:if($oAppArr.ClassId == $class.ClassId)}selected{/tpl:if}>{tpl:$class.name/}</option>
{/tpl:loop}
</select>
</td>
</tr>

<tr class="hover"><td>游戏描述</td>
<td align="left"><input type="text" class="span4" name="app_desc" value="{tpl:$oAppArr.app_desc/}" size="50" /></td>
</tr>


<tr class="hover"><td>官网</td>
<td align="left"><input type="text" class="span4" name="site_url" value="{tpl:$oAppArr.site_url/}" size="50" /></td>
</tr>

<tr class="hover"><td>兑换比例</td>
<td align="left"><input type="text" class="span4" name="exchange_rate" value="{tpl:$oAppArr.exchange_rate/}" size="50" /></td>
</tr>

<tr class="hover"><td>游戏币名称</td>
<td align="left"><input type="text" class="span4" name="comment[coin_name]" value="{tpl:$oAppArr.comment.coin_name/}" size="50" /></td>
</tr>

<tr class="hover"><td>是否由平台生成登录记录</td>
<td align="left">
	   <select name="comment[create_loginid]">
	   <option value="1"  {tpl:if($oAppArr.comment.create_loginid==1)}selected="selected"{/tpl:if}>平台生成</option>
	   <option value="0" {tpl:if($oAppArr.comment.create_loginid==0)}selected="selected"{/tpl:if}>游戏生成</option>
       </select>  
</td>
</tr>

<tr class="hover"><td>是否对外显示</td>
<td align="left">
	   <select name="is_show">
	   <option value="1"  {tpl:if($oAppArr.is_show==1)}selected="selected"{/tpl:if}>显示</option>
	   <option value="0" {tpl:if($oAppArr.is_show==0)}selected="selected"{/tpl:if}>不显示</option>
       </select>  
</td>
</tr>

<tr class="noborder"><td></td>
<td><button type="submit" id="app_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#app_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
			
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '产品ID不能为空，请修正后再次提交';
				errors[2] = '产品名称不能为空，请修正后再次提交';
				errors[3] = '产品标识不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改游戏成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#app_update_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}