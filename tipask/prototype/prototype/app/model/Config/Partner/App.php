<?php
/**
 * 合作商运营游戏
 * @author Chen<cxd032404@hotmail.com>
 * $Id: App.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Partner_App extends Base_Widget
{
	/**
	 * 表名
	 * @var string
	 */
	protected $table = 'config_partner_app';
	protected $table_permission = 'config_partner_permission';

	/**
	 * 插入数据
	 * @param array $parames
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除数据
	 * @param string $param
	 * @return boolean
	 */
	public function delete($bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`PartnerId` = ? and `AppId` = ?', $bind);
	}

	/**
	 * 修改数据
	 * @param array $bind
	 * @param string $param
	 */
	public function update(array $parame, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`PartnerId` = ? and `AppId` = ?', $parame);
	}

	/**
	 * 批量修改合作商数据
	 * @param int $PartnerId
	 * @param string $param
	 * @return boolean
	 */
	public function updatePartner($PartnerId, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`PartnerId` = ? ', $PartnerId);
	}
	
	/**
	 * 查询单个字段
	 * @param $param
	 * @param $field
	 * @return string
	 */
	public function getOne(array $param,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectOne($table_to_process, $fields, '`PartnerId` = ? and `AppId` = ?', $param);
	}

	/**
	 * 获取单行数据
	 * @param array $param
	 * @param $field
	 * @return array
	 */
	public function getRow(array $param, $field = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $field FROM $table_to_process WHERE `PartnerId` = ? and `AppId` = ?";
		return $this->db->getRow($sql, $param);
	}

		/**
	 * 按合作商ID查询
	 * @param $PartnerId
	 * @param $field
	 * @return array
	 */
	public function getPartnerAll($PartnerId, $field = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $field FROM $table_to_process WHERE `PartnerId` = ? ";
		return $this->db->getAll($sql, $PartnerId);
	}

	/**
	 * 按游戏ID查询
	 * @param $AppId
	 * @param $field
	 * @return array
	 */
	public function getAppAll($AppId, $field = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $field FROM $table_to_process WHERE `AppId` = ? ORDER BY PartnerId ASC";
		$PartnerList = array();
		$return = $this->db->getAll($sql, $AppId);
		foreach($return as $key => $value)
		{
			$PartnerList[$value['PartnerId']] = $value;	
		}
		return $PartnerList;
	}

	/**
	 * 查询全部
	 * @param $field
	 * @return array
	 */
	public function getAll($field = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $field FROM $table_to_process ORDER BY PartnerId ASC";
		return $this->db->getAll($sql);
	}
	/**
	 * 根据合作商的类型拼接语句
	 * @params partner_type 合作方式 1:官服/2:专区
	 * @return array
	 */
	public function getWherePartnerType($partner_type)    
	{
			switch($partner_type)
			{
				case 1:
				 $whereType = " PartnerId = 1 ";
				 break;
				case 2:
				 $whereType = " PartnerId >1";
				 break;
			 default:
				 $whereType = " PartnerId = 1";
				 break;
			}
			return $whereType;
	}
	/**
	 * 根据合作商的参数获取合作商列表
	 * @param $AppId 游戏
	 * @param $PartnerId 合作商
	 * @param $area 所在区域
	 * @param $whereType 查询运营模式的sql语句
	 * @return array
	 */
	public function getPartnerAppbyType($fields = "*",$AppId,$PartnerId,$AreaId,$whereType)
	{
			$AppId = abs(intval($AppId));
			$PartnerId = abs(intval($PartnerId));
			$AreaId = abs(intval($AreaId));
			$whereType = trim($whereType);

			$whereApp = $AppId?" AppId = $AppId":"";
			$wherePartner = $PartnerId?" PartnerId = $PartnerId":"";
			$whereArea = $AreaId?" AreaId = $AreaId":"";
			$whereCondition = array($whereApp,$wherePartner,$whereArea,$whereType);
			$where = Base_common::getSqlWhere($whereCondition);
			$table_name = Base_Widget::getDbTable($this->table);

			$sql = "select $fields from $table_name where 1".$where;
			return $this->db->getAll($sql);
	}
	//计算收益
	public function get_income($AppId,$PartnerId,$coin)
	{

		$partnerData = $this->getRow(array($PartnerId,$AppId),"income_type,income_rate");
		switch ($partnerData['income_type']) {
			case 1:
				return $this->mode_1($coin,$partnerData['income_rate']);
				break;
			case 2:
				$rateArr = Base_common::arr_process($partnerData['income_rate']);
				return $this->mode_2($coin,$rateArr);
				break;
			case 3:
				$rateArr = Base_common::arr_process($partnerData['income_rate']);
				return $this->mode_3($coin,$rateArr);
				break;
			default:
		}
	}
	/**
	 * 数组预处理
	 */
	public function arr_process($text)
	{
		$text_arr = explode("_",$text);
		foreach($text_arr as $key => $value)
		{
			$text_arr_2 = explode(",",$value);
			$arr[$text_arr_2[0]] = 	$text_arr_2[1];
		}
		krsort($arr);
		return $arr;
	}
	/**
	 * 分成比例计算公式1
	 */
	public function mode_1($num,$rate)
	{
		$rate = $rate?$rate:0;
		$data = array('cl_income'=>0,'partner_income'=>'0');
		$data['cl_income'] = $num * $rate;
		$data['partner_income'] = $num * ( 1-$rate );
		return($data);
	}

	/**
	 * 分成比例计算公式2
	 */
	public function mode_2($num,$rateArr)
	{
		krsort($rateArr);
		$data = array('cl_income'=>0,'partner_income'=>'0');
		foreach($rateArr as $key => $rate) {
			if($num > $key) {
					$rate = $rate?$rate:0;
					break;
			}
		}
		$data['cl_income'] = $num * $rate;
		$data['partner_income'] = $num * ( 1-$rate );

		return($data);
	}

	/**
	 * 分成比例计算公式3
	 */
	public function mode_3($num,$rateArr)
	{
		krsort($rateArr);
		$data = array('cl_income'=>0,'partner_income'=>'0');
		foreach($rateArr as $key => $rate) {
			$n = (($num-$key) > 0) ? ($num-$key) : 0;
			$num = $num-$n;
			$rate = $rate?$rate:0;
			$data['cl_income'] += $n * $rate;
			$data['partner_income'] += $n * (1-$rate);
		}

		return($data);
	}
	
	/**
	 * 获取当前游戏所有运营信息
	 * @param $AppId
	 * @param $fields
	 * @return array
	 */
	public function getByApp($AppId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process WHERE AppId = ?";
		return $this->db->getAll($sql,$AppId);
	}
	
	/**
	 * 根据所在地区和合作商类型筛选该组用户可查看的数据列表
	 * @param $partner
	 * @param $AreaId
	 * @return array
	 */
	public function getPermittedPartnerByPartnerType($partner_type,$partner_list)
	{
		$partner_type = intval($partner_type);
		if($partner_list)
		{
			foreach($partner_list as  $PartnerId => $partner_data)
			{
				if($partner_type>0)
				{
					if($partner_type==1)
					{
						if($PartnerId!=1)
						{
							unset ($partner_list[$PartnerId]);
						}
					}
					elseif($partner_type==2)
					{
						if(!($PartnerId!=1))
						{
							unset ($partner_list[$PartnerId]);
						}
					}
				}
			}
		}
		return $partner_list;
	}
	public function getPermittedPartnerByPartnerArea($AreaList,$partner_list)
	{
		if($partner_list)
		{
			foreach($partner_list as  $PartnerId => $partner_data)
			{
				if(!isset($AreaList[$partner_data['AreaId']]))
				{
					unset($partner_list[$PartnerId]);
				}
			}
		return $partner_list;
		}
	}
	public function reBuildPartnerAppConfig($fields = "*")
	{
		$AllApp = $this->getAll($fields);				
		$AllPartnerApp=array();
		foreach ($AllApp as $value) {
			
		 	$AllPartnerApp[$value['AppId']][$value['PartnerId']]=$value;
		} 
		$file_path = "../../etc/";
		$file_name = "PartnerApp.php";
		$var = var_export($AllPartnerApp,true);
		$text ='<?php $AllPartnerApp='.$var.'; return $AllPartnerApp;?>';		
		file_put_contents($file_path.$file_name,$text);
	}
	/*获取所有运营商所在地区
	 *@author selena 2013/3/13
	 * $AreaList是游戏所在的所有区域
	 */
	public function getAreaList()
	{
		$sql = "SELECT AppId,AreaId FROM {$this->table}";
		$PartnerList = array();
		$return = $this->db->getAll($sql);
		$AreaList = array();
		foreach($return as $key => $value)
		{
			$AreaList[$value["AppId"]][] = $value["AreaId"];	
		}
		return $AreaList;			
	}	
}