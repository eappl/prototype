{tpl:tpl header/}
</style>
<div id="maincontainer" class="clearfix">
    <header>
    	<div class="navbar navbar-fixed-top">
    		<div class="navbar-inner">
    			<div class="container-fluid">
                    <a href="http://www.google.com/" target="_blank" class="brand">
                        <i class="icon-home icon-white">
                    </i> <?php $this->config->companyName(); ?> <span class="sml_t"><?php $this->config->currentVersion(); ?></span></a>
    				<ul class="nav" id="mobile-nav">
                        {tpl:loop $menuArr $menu}
                            {tpl:loop $allowedMenuArr $purview}
                                {tpl:if($menu.menu_id == $purview.menu_id)} 
            						<li class="dropdown">
            							<a id="_block_{tpl:$menu.menu_id/}" href="javascript:getMenu('{tpl:$menu.menu_id/}');"><i class="icon-list-alt icon-white"></i> {tpl:$menu.name/} </a>
            						</li>
                                {/tpl:if}   
                            {/tpl:loop}	
                        {/tpl:loop}						
    				</ul>
    			</div>
    		</div>
    	</div>				
    </header>    
    <div id="contentwrapper">
        <div class="main_content"></div>
    </div>
    <a href="javascript:void(0)" class="sidebar_switch on_switch ttip_r" title="隐藏左栏">侧边栏开关</a>
    <div class="sidebar">	
    	<div class="antiScroll">
    		<div class="antiscroll-inner">
    			<div class="antiscroll-content">            
                    <div class="sidebar_inner">
                        <div class="br_bottom"></div>
                        <div class="sidebar_info">
                            <style type="text/css">
                            .unstyled li{
                                padding:5px;
                            }
                            </style>
                            <ul class="unstyled">
                                <li>
                                    <!--span class="act act-warning">65</span-->
                                    <h4>你好, <?php $this->manager->name(); ?></h4>
                                </li>
                                <li>
                                    <!--span class="act act-success">10</span-->
                                    <h5><a href="javascript:;" onclick="repwd('<?php $this->manager->id(); ?>');" title="修改密码">修改密码</a></h5>
                                </li>
                                <?php if($this->manager->name == "陈晓东"): ?>                                
                                <li>
                                    <!--span class="act act-success">10</span-->
                                    <h5><a href="/mysql/index.php" target="_blank" title="phpMyAdmin">phpMyAdmin</a></h5>
                                </li>
                                <?php endif; ?>                                                                
                                <li>
                                    <!--span class="act act-danger">85</span-->
                                    <h5><button class="btn btn-info" onclick="location.href='<?php $this->manager->logoutUrl(); ?>'">安全退出</button></h5>
                                </li>
                            </ul>
                        </div>
                        <div id="side_accordion" class="accordion">           		
                            <div class="push"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{tpl:tpl footer/}