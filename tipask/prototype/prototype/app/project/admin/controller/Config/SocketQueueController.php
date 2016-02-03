<?php
/**
 * Socket队列管理
 * @author chen<cxd032404@hotmail.com>
 * $Id: SocketQueueController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_SocketQueueController extends AbstractController
{
	/**
	 * 权限限制
	 * @var unknown_type
	 */
	protected $sign = '?ctl=config/socket.queue';
	/**
	 * App对象
	 * @var object
	 */
	protected $oServer;
	protected $oApp;
	protected $oPartner;
	protected $oPartnerApp;
    protected $oSocketType;

	/**
	 * 初始化对象
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oServer = new Config_Server();
		$this->oApp = new Config_App();
		$this->oPartner	= new Config_Partner();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oSocketQueue = new Config_SocketQueue();
        $this->oSocketType = new Config_SocketType();
	}

	/**
	 * 区服列表
	 * @return unknown_type
	 */
	public function indexAction()
	{
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
        /*$sendQueue = $this->oSocketQueue->getSendSocket('101001001','60215,60219,60217',1000);
        
		$CurrentQueue = $this->oSocketQueue->getAllByuType($uType);*/
        $uType = '60219';
        $CurrentQueue = $this->oSocketQueue->getAll();
        $SocketType = $this->oSocketType->getAll("Type,Name");
        foreach($CurrentQueue as $key=>&$val)
        {
            $val['uTypeName'] = $SocketType[$val['uType']]['Name'];
            if($val['uType']== $uType)
            {
                $MessegeContent = unserialize($val['MessegeContent']);
                $val['MessegeContent'] = $MessegeContent['MessegeContent'];
            }            
            $val['QueueTime'] = date('Y-m-d H:i:s',$val['QueueTime']);
        }
        
		include $this->tpl('Config_SocketQueue_index');
	}
    
    /**
	 * socket类型列表
	 * @return unknown_type
	 */
	public function indexTypeAction()
	{
		$sign = "?ctl=config/socket.queue&ac=index.type";
		//检查权限
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);
        
        $SocketType = $this->oSocketType->getAll();
        
		include $this->tpl('Config_SocketQueue_type_index');
	}

	/**
	 * 添加区服页面
	 * @return unknown_type
	 */
	public function addQueueAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$appArr = $this->oApp->getAll('AppId,name');
		$partnerArr = $this->oPartner->getAll('PartnerId,name');
		$AppId = $this->request->AppId;
		$PartnerId = $this->request->PartnerId;
        $uType = "60219";
		include $this->tpl('Config_SocketQueue_add');
	}
    
    /**
	 * 添加类型页面
	 * @return unknown_type
	 */
	public function addTypeAction()
	{
	    $sign = "?ctl=config/socket.queue&ac=index.type";
		//检查权限
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_INSERT);
        
		include $this->tpl('Config_SocketQueue_type_add');
	}

	/**
	 * 将数据入库
	 * @return unknown_type
	 */
	public function insertQueueAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$bind=$this->request->from('ServerId','AppId','PartnerId','StartTime','Lag','Count','CountDown','MessegeContent','uType');
		$bind['StartTime'] = strtotime($this->request->StartTime);
		//起始时间
		if ($bind['StartTime']<(time()-600))
		{
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}
		//发送次数
		if ($bind['Count']<=0)
		{
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}

		//倒记数
		if ($bind['CountDown']<0)
		{
			$response = array('errno' => 3);
			echo json_encode($response);
			return false;
		}
		//间隔
		if ($bind['Count']>1)
		{
		    if($bind['Lag']<0){
		      $response = array('errno' => 5);
			  echo json_encode($response);
			  return false;
		    }			
		}
		
		//倒记数
		if (trim($bind['MessegeContent'])=="")
		{
			$response = array('errno' => 4);
			echo json_encode($response);
			return false;
		}
		$ServerList = $this->oServer->getByAppPartner($bind['AppId'],$bind['PartnerId']);
        $socketType = $this->oSocketType->getRow($bind['uType']);
		$total_log = 0;
        
		foreach($ServerList as $Server => $ServerInfo)
		{
			if($bind['ServerId']>0)
			{
				if($Server == $bind['ServerId'])
				{
					$start_time = $bind['StartTime'];
					for($i=1;$i<=$bind['Count'];$i++)
					{
                        $CountDown = 0;
                        if($bind['Count'] == $i && !empty($bind['CountDown']) && $bind['CountDown'] > 0)
                        {
                            $CountDown = $bind['CountDown'];
                        }
                        
                        $bind['PackFormat'] = $socketType['PackFormat'];
                        $bind['Length'] = $socketType['Length'];
                        $bind['Length2'] = 0;
                        $bind['uType'] = $bind['uType'];
                        $bind['MsgLevel'] = 0;
                        $bind['Line'] = 0;
                        $bind['CountDown'] = $CountDown;
                        $bind['MessegeContent'] = $bind['MessegeContent'];
                        
                        $QueueTime = $bind['Count']>0?$start_time+($i-1)*$bind['Lag']:$start_time;
                        $queueArr = array('ServerId'=>$bind['ServerId'],
						'MessegeContent'=>serialize($bind),
                        'uType'=>$bind['uType'],'UserId'=>$QueueTime,
						'QueueTime'=>$QueueTime,
						);                        
                        
						$insert_log = $this->oSocketQueue->insert($queueArr);
						if($insert_log)
						{
							$total_log++;	
						}
					}
				}	
			}
			else
			{
				$start_time = $bind['StartTime'];
				for($i=1;$i<=$bind['Count'];$i++)
				{
					$QueueTime = $bind['Count']>0?$start_time+($i-1)*$bind['Lag']:$start_time;

					$queueArr = array('ServerIp'=>$ServerInfo['ServerIp'],
					'SocketPort'=>$ServerInfo['SocketPort'],
					'MessegeContent'=>$bind['MessegeContent'],
					'QueueTime'=>$start_time,'UserId'=>$QueueTime,
					'CountDown'=>$bind['CountDown']
					);
					$insert_log = $this->oSocketQueue->insert($queueArr);
					if($insert_log)
					{
						$total_log++;	
					}
				}
			}	
		}	
		$response = array('errno' => 0,'success'=>$total_log);
		echo json_encode($response);
		return true;
	}
    
    /**
	 * 将数据入库
	 * @return unknown_type
	 */
	public function insertTypeAction()
	{
	    $sign = "?ctl=config/socket.queue&ac=index.type";
		//检查权限
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$bind=$this->request->from('Type','Name','PackFormat','UnPackFormat','Length');
		
		//socket类型
		if (empty($bind['Type']))
		{
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}
        
        if (!is_int(intval($bind['Type'])))
		{
			$response = array('errno' => 3);
			echo json_encode($response);
			return false;
		}
        
        //socket类型
		if (empty($bind['Length']))
		{
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}
        
        if (!is_int(intval($bind['Length'])))
		{
			$response = array('errno' => 4);
			echo json_encode($response);
			return false;
		}
                
		$insert_log = $this->oSocketType->insert($bind);
		if($insert_log)
		{
            $this->oSocketType->reBuildSocketTypeConfig();	
		}	
	       
		$response = array('errno' => 0);
		echo json_encode($response);
		return true;
	}

    
    /**
	 * 修改页面
	 * @return unknown_type
	 */
	public function modifyTypeAction()
	{
	    $sign = "?ctl=config/socket.queue&ac=index.type";
        			
		//检查权限
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$Type = $this->request->Type;

		$SocketType = $this->oSocketType->getRow($Type);
        
		include $this->tpl('Config_SocketQueue_type_modify');
	}
    
    public function updateTypeAction()
	{
	    $sign = "?ctl=config/socket.queue&ac=index.type";
		//检查权限
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$bind=$this->request->from('Type','Name','PackFormat','UnPackFormat','Length');
		
		//socket类型
		if (empty($bind['Type']))
		{
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}
        
        //socket类型
		if (empty($bind['Length']))
		{
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}
        
		$update_log = $res = $this->oSocketType->update($bind['Type'], $bind);
        
        if($update_log)
        {
            $response = array('errno' => 0);
            $this->oSocketType->reBuildSocketTypeConfig();	
        }
        else
        {
            $response = array('errno' => 0);
        }	
	       
		
		echo json_encode($response);
		return true;
	}

    
    /**
	 * 删除数据
	 * @return unknown_type
	 */
	public function deleteTypeAction()
	{
		$sign = "?ctl=config/socket.queue&ac=index.type";		
		//检查权限
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$Type = intval($this->request->Type);
		$this->oSocketType->delete($Type);
        $this->oSocketType->reBuildSocketTypeConfig();	
		$this->response->goBack();
	}
    /**
	 * 删除socket队列数据
	 * @return unknown_type
	 */
	public function deleteAction()
	{		
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);		
		$queueId = intval($this->request->queueId);
        $uType = $this->oSocketQueue->getOne($queueId,'uType');
        if($uType == '60219')
        {
           $this->oSocketQueue->delete($queueId,$uType);		   
        }
		$this->response->goBack(); 
	}
	public function sendStatusAction()
	{
		$oPermission = new Config_Permission();

		$oApp = new Config_App();
		$oServer = new Config_Server();
		$oPartnerApp = new Config_Partner_App();
		$oPartner = new Config_Partner();
		$oArea = new Config_Area();
		

		//获取用户可以查看的游戏列表
		$permitted_app = $oPermission->getApp($this->manager->data_groups,'AppId,name');
		//预处理地区信息
		$AreaList = $oArea->getAll();
		//检查当前页面权限
		$sign = '?ctl=config/socket.queue&ac=send.status';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$SocketType = $this->oSocketType->getAll();

		//页面输入变量
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
		$ServerId = intval($this->request->ServerId);
		$AreaId = intval($this->request->AreaId)?intval($this->request->AreaId):0;
		$app_type = intval($this->request->app_type);
		$partner_type = intval($this->request->partner_type);
		$is_abroad = intval($this->request->is_abroad)?intval($this->request->is_abroad):0;
		$uType = intval($this->request->uType)?intval($this->request->uType):0;


		//时间范围初始化
		$Date= $this->request->Date?($this->request->Date):date("Y-m-d",time());
		
		//初始化图表配置
		$divideV = 23;
		$Step = 60;
    
		//初始化合作商列表
		$permitted_partner = array();
		//初始化服务器列表
		$permitted_server= array();
		
		//获取当前地区列表
		$AreaList = $oArea->getAbroad($is_abroad,$AreaList);
		//生成允许的地区id数组
		if($app_type>0)
		{
			//筛选是否平台产品
			$permitted_app = $oApp->getApp($app_type,$permitted_app);
		}
		if($AppId>0)
		{
		 	//获取可查看的权限总表
			$permitted_partner = $oPermission->getPartner($this->manager->data_groups,$AppId,'PartnerId,name,AreaId');
			//根据合作方式筛选
			$permitted_partner = $oPartnerApp->getPermittedPartnerByPartnerType($partner_type,$permitted_partner);
			//根据所在地区筛选
			$permitted_partner = $oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$permitted_partner);

			if($PartnerId>0)
			{
				//获取服务器列表
				$permitted_server = $oPermission->getServer($this->manager->data_groups,$AppId,$PartnerId,'ServerId,name');					
			}
		}
		//获取用于查询的权限sql语句
		$SendStatusArr = $this->oSocketQueue->getSendStatus($Date,$ServerId,$uType);
		for($i=0;$i<24*60;$i++)
		{
			$H = intval($i/60);
			$m = $i - $H * 60;
			if(!isset($SendStatusArr[$i]))
			{
				$SendStatusArr[$i] = array('Time'=>sprintf("%02d",$H).":".sprintf("%02d",$m),'SendCount'=>0);		
			}
			else
			{
			 	$SendStatusArr[$i]['Time'] = sprintf('%02d',$H).":".sprintf('%02d',$m);
			}	
		}
		ksort($SendStatusArr);
			 
		# Include FusionCharts PHP Class
		include('Third/fusion/Includes/FusionCharts_Gen.php');
		
		# Create Multiseries ColumnD chart object using FusionCharts PHP Class
		$FC = new FusionCharts("MsLine",'100%','500');
		
		# Set the relative path of the swf file
		$FC->setSWFPath("../Charts/");
		
		# Store chart attributes in a variable
		$strParam="caption='Socket队列发送情况';animation=0;xAxisName='时间';baseFontSize=12;numberPrefix=;decimalPrecision=0;showValues=0;formatNumberScale=0;labelStep=$Step;numvdivlines=$divideV;rotateNames=1;yAxisMinValue=0;yAxisMaxValue=10;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300'";
		
		# Set chart attributes
		$FC->setChartParams($strParam);
		foreach($SendStatusArr as $key => $data)
		{
			$FC->addCategory($data['Time']);
		}
		$FC->addDataset("发送数量");
		foreach($SendStatusArr as $key => $data)
		{
			$FC->addChartData($data['SendCount']);
		}
		
    	$page_title = "Socket队列发送情况";
		$page_form_action = $sign;
	 	//调取模板 
		include $this->tpl('Config_SocketQueue_SendStatus');
	}
}
