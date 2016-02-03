<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_settingcontrol extends base {

    function admin_settingcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load('operator');
		$this->load("menu");
		$this->load("worktime");		
    }

    function ondefault() {
        $this->onsetting();
    }

	/* 
	intoSetting:进入个人设置页面
	settingUpdate:修改个人设置
	*/
    function onsetting($msg='',$ty='') 
	{
		$hasIntoSettingPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoSetting");
		if ( $hasIntoSettingPrivilege['return'] )
		{	
			$login_name = isset($this->ask_login_name)?trim($this->ask_login_name):exit('<h2>非法登录<h2>');
			
			$msg && $message = $msg;
			$ty && $type = $ty;
			// 根据用户登录名 查询其口号、图片、
			$user_setting = $_ENV['operator']->getUser($login_name,1);
			include template('setting','admin');
		}
		else
		{
			$hasIntoSettingPrivilege['url'] = "?admin_main";
			__msg($hasIntoSettingPrivilege);
		
		}
    }
    /**
     * 根据传递来的用户名， 更新用户数据
		settingUpdate:修改个人设置权限
     */
    function onsetting_update(){
		$hasSettingUpdatePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "settingUpdate");
		if ( $hasSettingUpdatePrivilege['return'] )
		{
			$message = '';
			$img_type = array('image/pjpeg','image/x-png','image/png','image/gif','image/jpeg');
			if($_FILES['photo']['tmp_name'] !='' && is_uploaded_file($_FILES['photo']['tmp_name']))
			{
				if(!in_array($_FILES['photo']['type'],$img_type)) 
				{
					$message .= '对不起,只支持png,jpeg,gif格式的图片';
				}
				if($_FILES['photo']['size'] > 64*1024)
				{
					$message .= ' ;图片尺寸不能大于64k';
				} 
				$photo = addslashes(file_get_contents($_FILES['photo']['tmp_name']));
			}
			if($message != '')
			{
					$this->onsetting($message,'errormsg');
			}
			else
			{
					$qq =  $this->post['QQ'];
					$mobile = $this->post['mobile'];
					$weixin = $this->post['weixin'];
					$tel = $this->post['tel'];
					
					$_ENV['operator']->update($this->post['slogan'],$photo,$this->post['jobnumber'],$this->post['login_name'],$qq,$mobile,$weixin,$tel);
					$this->onsetting('更新成功');
			}
		}
		else
		{
			$hasSettingUpdatePrivilege['url'] = "?admin_setting/setting";
			__msg($hasSettingUpdatePrivilege);
		
		}
    }
    /**
     * ajax用主动去主站同步客服本人联系方式
     */
    function onview_rebuild_operator()
    {
		$operator = $this->ask_login_name;		
		$operatorInfo = $_ENV['operator']->getUser($operator);
		if($operatorInfo['login_name']!="")
		{
    		$rebuild = $_ENV['operator']->rebuildOperator($operatorInfo['login_name']);
    
    		if($rebuild>0)
    		{
    			//更新成功
    			$backReturn = array('type'=>'1','comment'=>"更新成功");
    		}
    		else
    		{
    			//更新失败
    			$backReturn = array('type'=>'0','comment'=>$operator."更新失败或Vadmin无关联账号");   
    		}		        
		}
		else
		{
            $backReturn = array('type'=>'0','comment'=>"无此账号"); 
        }

		echo(json_encode($backReturn));
    }
	
		//客服忙碌状态更新
    function onhandle_busy_status()
	{
		$login_name = $this->ask_login_name;
		$operator_info = $_ENV['operator']->getByColumn('login_name',$login_name);
		if(($operator_info['ishandle'] == 1) && ($operator_info['isonjob'] == 1))
		{
			//非忙碌->忙碌
			if($operator_info['isbusy']==0)
			{
				$isbusy = 1;
				$update = $_ENV['operator']->updateOperatorByName($login_name,array('isbusy'=>$isbusy));
				if($update>0)
				{
					echo 1;
					$_ENV['worktime']->worktimeModify($login_name,1,1);
				}
				else
				{
					echo 0;
				}
			}
			//忙碌->非忙碌
			else
			{
				$isbusy = 0;
				$update = $_ENV['operator']->updateOperatorByName($login_name,array('isbusy'=>$isbusy));
				$update = 1;
				if($update>0)
				{
					echo 0;
					$_ENV['worktime']->worktimeModify($login_name,1,0);
				}
				else
				{
					echo 1;
				}
			}		
		}
		else
		{
			echo $operator_info['isbusy']."<br>";
		}
	}
	
}

?>
