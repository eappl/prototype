<?php
/**
 * FAQ管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: FaqController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_FaqController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/faq';
	/**
	 * Faq对象
	 * @var object
	 */
	protected $oFaq;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oFaq = new Config_Faq();
		$this->oFaqType = new Config_Faq_Type();
	}
	//FAQ配置列表页面
	public function indexAction()
	{
		$FaqTypeList = $this->oFaqType->getAll('name,FaqTypeId');
		$FaqTypeId = $this->request->FaqTypeId?abs(intval($this->request->FaqTypeId)):0;
		
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$FaqArr = $this->oFaq->getAll($FaqTypeId);
		if($FaqArr)
		{
			foreach($FaqArr as $FaqId => $FaqData)
			{
				$FaqArr[$FaqId]['FaqTypeName'] = $FaqTypeList[$FaqData['FaqTypeId']]['name'];	
			}
		}
		include $this->tpl('Config_Faq_list');
	}
	//添加FAQ填写配置页面
	public function addAction()
	{
	    include('Third/ckeditor/ckeditor.php');
        
        $editor =  new CKEditor();
		$editor->BasePath = '/js/ckeditor/';
		$editor->config['height'] = 150;
		$editor->config['width'] =700;
        
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$FaqTypeList = $this->oFaqType->getAll('name,FaqTypeId');
		include $this->tpl('Config_Faq_add');
	}
	
	//添加新FAQ
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);

		$bind=$this->request->from('name','FaqTypeId','Answer');


		if($bind['FaqTypeId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}
		elseif($bind['Answer']=='')
		{
			$response = array('errno' => 3);
		}		
		else
		{	
			$res = $this->oFaq->insert($bind);
			$response = $res ? array('errno' => 0,'FaqTypeId'=>$bind['FaqTypeId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改FAQ信息页面
	public function modifyAction()
	{
	    include('Third/ckeditor/ckeditor.php');
        
        $editor =  new CKEditor();
		$editor->BasePath = '/js/ckeditor/';
		$editor->config['height'] = 150;
		$editor->config['width'] =700;
       
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$FaqId = $this->request->FaqId;
		$FaqTypeId = $this->request->FaqTypeId;
		$Faq = $this->oFaq->getRow($FaqId,'*');
		$FaqTypeList = $this->oFaqType->getAll('name,FaqTypeId');
		include $this->tpl('Config_Faq_modify');
	}
	
	//更新FAQ信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('FaqId','name','FaqTypeId','Answer');

		
		if($bind['FaqId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['FaqTypeId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		elseif($bind['Answer']=='')
		{
			$response = array('errno' => 4);
		}	
		else
		{	
			$res = $this->oFaq->update($bind['FaqId'], $bind);
			$response = $res ? array('errno' => 0,'FaqTypeId'=>$bind['FaqTypeId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除FAQ
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$FaqId = intval($this->request->FaqId);
		$this->oFaq->delete($FaqId);
		$this->response->goBack();
	}
}
