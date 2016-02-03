<?php
/**
 * 广告商管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: DetailController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_DetailController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/source/detail';
	/**
	 * Source对象
	 * @var object
	 */
	protected $oSource;
	protected $oSourceDetail;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSource = new Config_Source();
		$this->oSourceDetail = new Config_Source_Detail();
		$this->oSourceType = new Config_Source_Type();
        $this->oSourceProject = new Config_Source_Project();

		$this->SourceTypeList = $this->oSourceType->getAll();

	}
	//广告位配置列表页面
	public function indexAction()
	{

		$SourceId = intval($this->request->SourceId);
		$SourceTypeId = intval($this->request->SourceTypeId);	
		$SourceTypeList = $this->SourceTypeList;
		$SourceList =  $this->oSource->getAll($SourceTypeId);
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		if($SourceId)
		{
			$DetailArr = $this->oSourceDetail->getAll(array($SourceId=>1));
		}
		else
		{
			$DetailArr = $this->oSourceDetail->getAll($SourceList);
		}
		if($DetailArr)
		{
				foreach($DetailArr as $Detail => $SourceDetailData)
				{
					$Type = $SourceList[$SourceDetailData['SourceId']]['SourceTypeId'];
					$SourceDetailArr[$Type][$SourceDetailData['SourceId']][$Detail] = $SourceDetailData;
					$SourceDetailArr[$Type][$SourceDetailData['SourceId']][$Detail]['SourceTypeName'] = $SourceTypeList[$Type]['name'];	
					$SourceDetailArr[$Type][$SourceDetailData['SourceId']][$Detail]['SourceName'] = $SourceList[$SourceDetailData['SourceId']]['name'];
				}			
		}	
		include $this->tpl('Config_Source_Detail_list');
	}
	//添加广告商填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$SourceTypeList = $this->SourceTypeList;
		$SourceList =  $this->oSource->getAll($SourceTypeId);
        $SourceProjectArr = $this->oSourceProject->getAll();
		include $this->tpl('Config_Source_Detail_add');
	}
	
	//添加新广告商
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('Source','name','is_join','SourceProjectId','StartDate','EndDate','Cost');
		$data['SourceId'] = intval($bind['Source']);
        //项目相关参数
        $project_data['SourceProjectId'] = $bind['SourceProjectId'] ;
        $project_data['SourceId'] = $data['SourceId'] ;                
        $project_data['StartDate'] = $bind['StartDate'] ;
        $project_data['EndDate'] = $bind['EndDate'] ;
        $project_data['Cost'] = $bind['Cost'] ;
         
           
		if($bind['name']=='')
		{
			$response = array('errno' => 2);
		}
		elseif($data['SourceId']==0)
		{
			$response = array('errno' => 1);
		}		
		else
		{		
		    $keywords_array = explode("\n",$bind['name']);
            $count = count($keywords_array);
		    if($count>1)
            {
                //$success = 0;
                //$error = 0;
                                
                foreach($keywords_array as $k=>$v)
                {
                    $keywords = explode("|",$v);
                    $data['SourceDetail'] = $keywords[1];
                    $data['name'] = $keywords[0];                      
                    $SourceDetail = $this->oSourceDetail->replace($data);//replace存在就update更新 不存在就insert id是自增  返回id                    
                    //!empty($res)?$success++: $error++;
                    
                    // 是否加入项目
                    if(!empty($bind['is_join']))
                    {
                        
                        //1.判断是否在表中存在  不存在则insert  存在则 update  不能用replace  因为 广告位id(SourceDetail)不是主键
                        $project_data['SourceDetail'] = $SourceDetail ;
                        $res = $this->oSourceProject->replaceDetail($project_data);
                    }      
                }                     
                //$response = $count ? array('errno' => 0,'SourceId'=>$data['SourceId'],'SourceTypeId'=>intval($this->request->TypeId),'count'=>$count,'success'=>$success,'error'=>$error) : array('errno' => 9);           
                $response = $SourceDetail ? array('errno' => 0,'SourceId'=>$data['SourceId'],'SourceTypeId'=>intval($this->request->TypeId)) : array('errno' => 9);
            }else
            {
                $data['name'] = $bind['name'];
                $SourceDetail = $this->oSourceDetail->insert($data);
                // 是否加入项目
                if(!empty($bind['is_join']))
                {
                    $project_data['SourceDetail'] = $SourceDetail ;
                    //因为是根据 广告位id(SourceDetail)来判断的 所以单个添加 逐渐自增 不存在会重复的问题	
                    $res = $this->oSourceProject->insertDetail($project_data);
                }  
                $response = $SourceDetail ? array('errno' => 0,'SourceId'=>$data['SourceId'],'SourceTypeId'=>intval($this->request->TypeId)) : array('errno' => 9);
            }	
		}
		echo  json_encode($response);
        return true;
	}
	
	//修改广告商信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SourceDetail = trim($this->request->SourceDetail);
		$SourceTypeList = $this->SourceTypeList;
		$SourceDetailData = $this->oSourceDetail->getRow($SourceDetail ,'*');	
		$SourceInfo =  $this->oSource->getRow($SourceDetailData['SourceId']);

		$SourceDetailData['SourceTypeId'] = $SourceInfo['SourceTypeId'];
		$SourceList =  $this->oSource->getAll($SourceDetailData['SourceTypeId']);
		include $this->tpl('Config_Source_Detail_modify');
	}
	
	//更新广告商信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('Source','name');
		$bind['SourceId'] = intval($bind['Source']);
		
		unset($bind['Source']);
		$SourceDetail = $this->request->SourceDetail;
		if($bind['SourceId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSourceDetail->update($SourceDetail, $bind);
			$response = $res ? array('errno' => 0,'SourceId'=>$bind['SourceId'],'SourceTypeId'=>intval($this->request->TypeId)) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除广告商
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$SourceDetail = trim($this->request->SourceDetail);
		$this->oSourceDetail->delete($SourceDetail);
		$this->response->goBack();
	}
	/**
	 * 获取广告商列表
	 * @params SourceTypeId 广告商分类列表 
	 * @return 下拉列表
	 */
	public function getDetailBySourceAction()
	{
		$SourceId = $this->request->SourceId?abs(intval($this->request->SourceId)):0;
		$SourceDetailArr = $this->oSourceDetail->getAll(array($SourceId=>$SourceId));
		echo "<option value=0>全部</option>";
		if($SourceId > 0)
		{
			if(count($SourceDetailArr))
			{
					foreach($SourceDetailArr as $Detail => $SourceDetailData)
					{
								echo "<option value='{$Detail}'>{$SourceDetailData['name']}</option>";
					}
				}
			}
			else
			{
				
			}
	}
}
