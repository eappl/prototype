<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>运营综合数据平台</title>
    
        <!-- Bootstrap framework -->
            <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  />
            <link rel="stylesheet" href="bootstrap/css/bootstrap-responsive.min.css"  />
        <!-- jQuery UI theme -->
            <link rel="stylesheet" href="lib/jquery-ui/css/Aristo/Aristo.css"  />
        <!-- theme color-->
            <link rel="stylesheet" href="style/blue.css"  id="link_theme" />
        <!-- tooltips-->
            <link rel="stylesheet" href="lib/jBreadcrumbs/css/BreadCrumb.css"  />
        <!-- tooltips-->
            <link rel="stylesheet" href="lib/qtip2/jquery.qtip.min.css"  />
		<!-- colorbox -->
            <link rel="stylesheet" href="lib/colorbox/colorbox.css"  />
        <!-- code prettify -->
            <link rel="stylesheet" href="lib/google-code-prettify/prettify.css"  />    
        <!-- notifications -->
            <link rel="stylesheet" href="lib/sticky/sticky.css"  />    
        <!-- aditional icons -->
            <link rel="stylesheet" href="img/splashy/splashy.css"  />
		<!-- flags -->
            <link rel="stylesheet" href="img/flags/flags.css"  />	
		<!-- calendar -->
            <link rel="stylesheet" href="lib/fullcalendar/fullcalendar_gebo.css"  />	
		<!-- datepicker -->
            <link rel="stylesheet" href="lib/datepicker/datepicker.css"  />
        <!-- tag handler -->
            <link rel="stylesheet" href="lib/tag_handler/css/jquery.taghandler.css"  />
        <!-- uniform -->
            <link rel="stylesheet" href="lib/uniform/Aristo/uniform.aristo.css"  />
		<!-- multiselect -->
            <link rel="stylesheet" href="lib/multi-select/css/multi-select.css"  />
		<!-- enhanced select -->
            <link rel="stylesheet" href="lib/chosen/chosen.css"  />
        <!-- wizard -->
            <link rel="stylesheet" href="lib/stepy/css/jquery.stepy.css"  />
        <!-- upload -->
            <link rel="stylesheet" href="lib/plupload/js/jquery.plupload.queue/css/plupload-gebo.css"  />
		<!-- CLEditor -->
            <link rel="stylesheet" href="lib/CLEditor/jquery.cleditor.css"  />
		<!-- colorpicker -->
            <link rel="stylesheet" href="lib/colorpicker/css/colorpicker.css"  />
		<!-- smoke_js -->
            <link rel="stylesheet" href="lib/smoke/themes/gebo.css"  />
			
        <!-- main styles -->
            <link rel="stylesheet" href="style/style.css"  />
            
        <!-- favicon -->
            <link rel="shortcut icon" href="favicon.ico" />            
		
        <!--[if lte IE 8]>
            <link rel="stylesheet" href="style/ie.css"  />
        <![endif]-->
        	
        <!--[if lt IE 9]>
			<script src="js/ie/html5.js" ></script>
			<script src="js/ie/respond.min.js" ></script>
			<script src="lib/flot/excanvas.min.js" ></script>
        <![endif]-->
		<script>
			//* hide all elements & show preloader
			document.documentElement.className += 'js';
		</script>        
    </head>
    <body>
    <div id="loading_layer" style="display:none"><img src="img/ajax_loader.gif"  alt="" /></div>
    <div class="style_switcher">		
		<div class="sepH_c">
			<p>边栏位置:</p>
			<div class="clearfix">
				<label class="radio inline"><input type="radio" name="ssw_sidebar" id="ssw_sidebar_left" value="" checked /> 左边</label>
				<label class="radio inline"><input type="radio" name="ssw_sidebar" id="ssw_sidebar_right" value="sidebar_right" /> 右边</label>
			</div>
		</div>		
		<div class="gh_button-group">
			<a href="#" id="resetDefault" class="btn btn-mini">重置</a>
		</div>
	</div>