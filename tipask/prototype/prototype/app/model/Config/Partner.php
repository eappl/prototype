<?php
/**
 * 合作商数据
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Partner.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Config_Partner extends Base_Widget
{
	/**
	 * 表名
	 * @var string
	 */
	protected $table = 'config_partner';

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
	 * @param $PartnerId
	 * @return boolean
	 */
	public function delete($PartnerId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`PartnerId` = ?', $PartnerId);
	}

	/**
	 * 修改数据
	 * @param $PartnerId
	 * @param $bind
	 * @return array
	 */
	public function update($PartnerId, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`PartnerId` = ?', $PartnerId);
	}

	/**
	 * 查询单个字段
	 * @param $PartnerId
	 * @param $field
	 * @return string
	 */
	public function getOne($PartnerId, $field)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectOne($table_to_process, $field, '`PartnerId` = ?', $PartnerId);
	}

	/**
	 * 获取单条数据
	 * @param $param
	 * @param $fields
	 * @return array
	 */
	public function getRow($PartnerId, $fields = "*")
	{				
		$PartnerId = intval($PartnerId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`PartnerId` = ?', $PartnerId);
	}



	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$return = $this->db->select($table_to_process, $fields);
		foreach($return as $key => $value)
		{
			$PartnerList[$value['PartnerId']] = $value;	
		}
		return $PartnerList;
	}
	
	/**
	 * 合作商分类
	 * @param $partner
	 * @param $oPartnerPermission
	 * @return array
	 */
	public function getPartner($partner,$oPartnerPermission)
	{
		$partner = intval($partner);

		if(count($oPartnerPermission))
		{
			foreach($oPartnerPermission as $key => $partner_data)
			{
					if($partner>0)
					{
						if($partner==1)
						{
							if($partner_data['PartnerId']!=1)
							{
								unset ($oPartnerPermission[$key]);
							}
						}
						elseif($partner==2)
						{
							if(!($partner_data['PartnerId']!=1))
							{

								unset ($oPartnerPermission[$key]);
							}	
						}
						elseif($partner==3)
						{
							if(!($partner_data['PartnerId']!=1))
							{
								unset ($oPartnerPermission[$key]);
							}	
						}
					}
				
			}
		}
		return $oPartnerPermission;
	}
	public function reBuildPartnerConfig($fields = "*")
	{
		$AllPartner = $this->getAll($fields);
		$file_path = "../../etc/";
		$file_name = "Partner.php";
		$var = var_export($AllPartner,true);
		$text ='<?php $AllPartner='.$var.'; return $AllPartner;?>';		
		file_put_contents($file_path.$file_name,$text);
			
	}
}