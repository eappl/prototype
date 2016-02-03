<!DOCTYPE html>
<html lang="en" class="login_page">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>运营综合数据平台</title>
    
        <!-- Bootstrap framework -->
            <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  />
            <link rel="stylesheet" href="bootstrap/css/bootstrap-responsive.min.css"  />
        <!-- theme color-->
            <link rel="stylesheet" href="style/blue.css"  />
        <!-- tooltip -->    
			<link rel="stylesheet" href="lib/qtip2/jquery.qtip.min.css"  />
        <!-- main styles -->
            <link rel="stylesheet" href="style/style.css"  />
    
        <!--[if lt IE 9]>
            <script src="js/ie/html5.js" ></script>
			<script src="js/ie/respond.min.js" ></script>
        <![endif]-->
		
    </head>
    <body>
		
		<div class="login_box">			
			<form action="<?php $this->manager->loginAction(); ?>" method="post" id="login_form">
				<div class="top_b">登录</div>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-login">
                        用户名或密码错误
    				</div>
                <?php else: ?>    
    				<div class="alert alert-info alert-login">
                        请输入用户名和密码
    				</div>
                <?php endif; ?>
				<div class="cnt_b">
					<div class="formRow">
						<div class="input-prepend">
							<span class="add-on"><i class="icon-user"></i></span><input type="text" id="name" name="name" placeholder="用户名" value="" />
						</div>
					</div>
					<div class="formRow">
						<div class="input-prepend">
							<span class="add-on"><i class="icon-lock"></i></span><input type="password" id="passwd" name="passwd" placeholder="密码" value="" />
						</div>
					</div>
				</div>
				<div class="btm_b clearfix">
					<button class="btn btn-inverse pull-right" type="submit">登录</button>
                    <input type="hidden" name="referer" value="<?php echo $referer; ?>" />                                        
				</div>  
			</form>			
		</div>
		 
        <script src="js/jquery.min.js" ></script>
        <script src="js/jquery.actual.min.js" ></script>
        <script src="lib/validation/jquery.validate.js" ></script>
		<script src="bootstrap/js/bootstrap.min.js" ></script>
        
        <script>
            $(document).ready(function(){
                
				//* boxes animation
				form_wrapper = $('.login_box');
				function boxHeight() {
					form_wrapper.animate({ marginTop : ( - ( form_wrapper.height() / 2) - 24) },400);	
				};
				form_wrapper.css({ marginTop : ( - ( form_wrapper.height() / 2) - 24) });
                $('.linkform a,.link_reg a').on('click',function(e){
					var target	= $(this).attr('href'),
						target_height = $(target).actual('height');
					$(form_wrapper).css({
						'height'		: form_wrapper.height()
					});	
					$(form_wrapper.find('form:visible')).fadeOut(400,function(){
						form_wrapper.stop().animate({
                            height	 : target_height,
							marginTop: ( - (target_height/2) - 24)
                        },500,function(){
                            $(target).fadeIn(400);
                            $('.links_btm .linkform').toggle();
							$(form_wrapper).css({
								'height'		: ''
							});	
                        });
					});
					e.preventDefault();
				});
				
				//* validation
				$('#login_form').validate({
					onkeyup: false,
					errorClass: 'error',
					validClass: 'valid',
					rules: {
						name: { required: true, minlength: 2 },
						passwd: { required: true, minlength: 3 }
					},
					highlight: function(element) {
						$(element).closest('div').addClass("f_error");
						setTimeout(function() {
							boxHeight()
						}, 200)
					},
					unhighlight: function(element) {
						$(element).closest('div').removeClass("f_error");
						setTimeout(function() {
							boxHeight()
						}, 200)
					},
					errorPlacement: function(error, element) {
						$(element).closest('div').append(error);
					}
				});
            });
        </script>			
    </body>
</html>

