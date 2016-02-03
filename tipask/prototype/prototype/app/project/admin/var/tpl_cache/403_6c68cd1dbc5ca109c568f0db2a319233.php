<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
var message = "<?php echo $PermissionCheck['message']; ?>";
divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $home; ?>');}});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>