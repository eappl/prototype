{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_catalog_update_form" name="race_catalog_update_form" action="{tpl:$this.sign/}&ac=race.catalog.update" metdod="post">
<input type="hidden" name="RaceCatalogId" value="{tpl:$oRaceCatalog.RaceCatalogId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>赛事名称</td>
<td align="left"><input name="RaceCatalogName" type="text" class="span4" id="RaceCatalogName" value="{tpl:$oRaceCatalog.RaceCatalogName/}" size="50" /></td>
</tr>
<tr class="hover"><td>赛事ID</td>
<td align="left">{tpl:$oRaceCatalog.RaceCatalogId/}</td>
</tr>
	<tr class="hover"><td>赛事图标</td>
		<td align="left"><input name="RaceCatalogIcon[1]" type="file" class="span4" id="RaceCatalogIcon[1]" /></td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="race_catalog_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#race_catalog_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改赛事成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#race_catalog_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}