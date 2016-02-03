<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); if(CURMODULE != 'logging') { ?>
<script src="<?php echo $_G['setting']['jspath'];?>logging.js?<?php echo VERHASH;?>" type="text/javascript"></script>
<div class="fastlg cl">
<span id="return_ls" style="display:none"></span>
<div class="y pns">
<table cellspacing="0" cellpadding="0">
<tr>
<td><button tabindex="1" class="pn pnc" onclick = "location.href = 'http://passport.5173.com?returnUrl='+encodeURIComponent(encodeURIComponent(location.href));"><strong>登录</strong></button></td>
<td><button tabindex="1" class="pn pnc" onclick = "location.href = 'https://passport.5173.com/User/Register?returnUrl='+encodeURIComponent(encodeURIComponent(location.href));"><strong><?php echo $_G['setting']['reglinkname'];?></strong></button></td>
</tr>
</table>
</div>
<?php if(!empty($_G['setting']['pluginhooks']['global_login_extra'])) echo $_G['setting']['pluginhooks']['global_login_extra'];?>
</div>
<?php if($_G['setting']['pwdsafety']) { ?>
<script src="<?php echo $_G['setting']['jspath'];?>md5.js?<?php echo VERHASH;?>" type="text/javascript" reload="1"></script>
<?php } } ?>