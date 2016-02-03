<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-CN" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>数据中心后台管理</title>
<script type="text/javascript" src="{wee:$config.js/}jquery.js"></script>
<script type="text/javascript" src="{wee:$config.js/}jquery/common.js"></script>
<script type="text/javascript" src="{wee:$config.js/}jquery/form.js"></script>
<script type="text/javascript" src="{wee:$config.js/}jquery/bgiframe.js"></script>
<script type="text/javascript" src="{wee:$config.js/}jquery/wee/weebox.js"></script>
<link type="text/css" rel="stylesheet" href="{wee:$config.js/}jquery/wee/weebox.css" />
<link type="text/css" rel="stylesheet" href="{wee:$config.style/}admincp.css" />
</head>
<body>
<div id="wrap">
<?php if ($this->notice->message):?>
<div class="<?php $this->notice->type();?>"><?php $this->notice->message();?></div>
<?php endif;?>