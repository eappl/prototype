{tpl:tpl contentHeader/}
<script type="text/javascript">
var message = "{tpl:$PermissionCheck.message/}";
divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$home/}');}});
</script>
{tpl:tpl contentFooter/}