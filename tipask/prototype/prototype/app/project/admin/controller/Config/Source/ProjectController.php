<?php
/**
 * 推广项目管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: ProjectController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_ProjectController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/source/project';
	/**
	 * SourceProject对象
	 * @var object
	 */
	protected $oSourceDetail;
	protected $oSource;
	protected $oSourceProject;
	protected $oSourceType;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSourceProject = new Config_Source_Project();
		$this->oSourceDetail = new Config_Source_Detail();
		$this->oSource = new Config_Source();
		$this->oSourceType = new Config_Source_Type();

		$this->SourceTypeList = $this->oSourceType->getAll();


	}
	//项目列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$SourceProjectArr = $this->oSourceProject->getAll();
		include $this->tpl('Config_Source_Project_list');
	}
	//添加推广项目写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('Config_Source_Project_add');
	}
	
	//添加推广项目渠道
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name');


		if($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSourceProject->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改推广项目页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SourceProjectId = intval($this->request->SourceProjectId);
		$SourceProject = $this->oSourceProject->getRow($SourceProjectId,'*');
		include $this->tpl('Config_Source_Project_modify');
	}
	
	//更新推广项目信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('SourceProjectId','name');

		
		if($bind['SourceProjectId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSourceProject->update($bind['SourceProjectId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除推广项目
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$SourceProjectId = intval($this->request->SourceProjectId);
		$this->oSourceProject->delete($SourceProjectId);
		$this->response->goBack();
	}
	//修改推广项目详情页面
	public function detailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SourceProjectId = intval($this->request->SourceProjectId);
		$SourceProject = $this->oSourceProject->getRow($SourceProjectId,'*');
		$SourceProjectDetail = $this->oSourceProject->getDetail($SourceProjectId);
		$SourceList = $this->oSource->getAll();
		$SourceDetailList = $this->oSourceDetail->getAll(0);        
        //下载参数
        $export = $this->request->export?intval($this->request->export):0;
        
        $links = 'http://passport.wjyx.com/?c=media&PageId=2';
		foreach($SourceProjectDetail as $key => $value)
		{
			$SourceProjectDetail[$key]['SourceName'] = $SourceList[$value['SourceId']]['name'];
			$SourceProjectDetail[$key]['SourceDetailName'] = $value['SourceDetail']?$SourceDetailList[$value['SourceDetail']]['name']:"<font color = 'red'>全部</font>";
			$SourceProjectDetail[$key]['SourceUrl'] = $links."&UserSourceId=".$value['SourceId']."&UserSourceDetail=".$value['SourceDetail']."&UserSourceProjectId=".$value['SourceProjectId'];
			//$SourceProjectDetail[$key]['SourceUrl'] = "&UserSourceId=".$value['SourceId']."UserSourceDetail".$value['SourceDetail']."UserSourceProjectId".$value['SourceProjectId'];
//			$SourceProjectDetail[$key]['SourceUrl'] = Base_Common::my_authcode($SourceProjectDetail[$key]['SourceUrl'],'','limaogame');
			
		}
       
        $param['SourceProjectId'] =  $SourceProjectId;
        $execlParam = $param+array("export"=>1);
		$export_var = "<a href =".(Base_Common::getUrl('','config/source/project','detail',$execlParam))."><导出表格></a>";
        if($export==1)
		{			
			$oExcel = new Third_Excel();

			$FileName='广告位列表--'.$SourceProject['name'];
			//标题栏
			$title = array("广告商","广告位","连接参数");

			$oExcel->download($FileName)->addSheet('广告位列表');
			$oExcel->addRows(array($title));
			
		 	foreach($SourceProjectDetail as $key =>$sourceproject_detail)
			{
				//生成单行数据

				$t['SourceName'] = $sourceproject_detail['SourceName'];
				$t['SourceDetailName'] = $sourceproject_detail['SourceDetailName'];
				$t['SourceUrl'] = $sourceproject_detail['SourceUrl'];
												
				$oExcel->addRows(array($t));	
				unset($t);					
			}

			//结束excel
			$oExcel->closeSheet()->close();							
		}	
        
		include $this->tpl('Config_Source_Project_Detail_list');
	}
	//修改推广项目详情页面
	public function modifyDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SourceProjectId = intval($this->request->SourceProjectId);
		$SourceProjectDetailId = intval($this->request->SourceProjectDetailId);
		$SourceProject = $this->oSourceProject->getRow($SourceProjectId,'*');
		$SourceProjectSingleDetail = $this->oSourceProject->getSingleDetail($SourceProjectId,$SourceProjectDetailId,'*');
		$SourceInfo =  $this->oSource->getRow($SourceProjectSingleDetail['SourceId']);
		$SourceProjectSingleDetail['SourceTypeId'] = $SourceInfo['SourceId'];
		$SourceList =  $this->oSource->getAll($SourceInfo['SourceTypeId']);
		$SourceTypeList = $this->SourceTypeList;

		$SourceDetailList = $this->oSourceDetail->getAll(array($SourceInfo['SourceId']=>$SourceInfo));
		include $this->tpl('Config_Source_Project_Detail_modify');
	}
	//更新推广项目详情信息
	public function updateDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('SourceProjectId','SourceProjectDetailId','SourceId','SourceDetail','StartDate','EndDate','Cost');
		
		$res = $this->oSourceProject->updateDetail($bind['SourceProjectId'],$bind['SourceProjectDetailId'],$bind);
		
		if($bind['SourceProjectId']==0)
		{
			$response = array('errno' => 2);
		}
		elseif($bind['SourceProjectDetailId']==0)
		{
			$response = array('errno' => 3);
		}	
		elseif($bind['SourceId']==0)
		{
			$response = array('errno' => 4);
		}	
		elseif($bind['SourceDetail']<=0)
		{
			$response = array('errno' => 5);
		}	
		elseif(strtotime($bind['StartDate'])==0)
		{
			$response = array('errno' => 6);
		}	
		elseif(strtotime($bind['EndDate'])==0)
		{
			$response = array('errno' => 7);
		}	
		elseif($bind['Cost']<0)
		{
			$response = array('errno' => 8);
		}	
		else
		{	
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//删除推广项目
	public function deleteDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$SourceProjectId = intval($this->request->SourceProjectId);
		$SourceProjectDetailId = intval($this->request->SourceProjectDetailId);
		$this->oSourceProject->deleteDetail($SourceProjectId,$SourceProjectDetailId);
		$this->response->goBack();
	}
	//修改推广项目页面
	public function addDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SourceProjectId = intval($this->request->SourceProjectId);
		$SourceProject = $this->oSourceProject->getRow($SourceProjectId,'*');
		$SourceList = $this->oSource->getAll();
		$SourceDetailList = $this->oSourceDetail->getAll(0);
		$SourceTypeList = $this->SourceTypeList;
		include $this->tpl('Config_Source_Project_Detail_add');
	}
	//更新推广项目详情信息
	public function insertDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('SourceProjectId','SourceId','SourceDetail','StartDate','EndDate','Cost');
		
		$res = $this->oSourceProject->insertDetail($bind);
		
		if($bind['SourceProjectId']==0)
		{
			$response = array('errno' => 2);
		}
		elseif($bind['SourceId']==0)
		{
			$response = array('errno' => 4);
		}	
		elseif($bind['SourceDetail']<=0)
		{
			$response = array('errno' => 5);
		}	
		elseif(strtotime($bind['StartDate'])==0)
		{
			$response = array('errno' => 6);
		}	
		elseif(strtotime($bind['EndDate'])==0)
		{
			$response = array('errno' => 7);
		}	
		elseif($bind['Cost']<0)
		{
			$response = array('errno' => 8);
		}	
		else
		{	
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
}
