<?php
/**
 * 测试用户管理
 * @author 陈晓东
 * $Id: TestUser.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_TestUser extends Base_Widget
{
	/**
	 * 表名
	 * @var string
	 */
	protected $table = 'config_test_user';

	/**
	 * 初始化表名
	 * @return string
	 */
	public function init()
	{
		parent::init();
		$this->table = Base_Widget::getDbTable($this->table);
	}

	/**
	 * 插入数据
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert($this->table, $bind);
	}

	/**
	 * 删除数据
	 * @param string $username
	 * @return boolean
	 */
	public function delete(array $bind)
	{
		return $this->db->delete($this->table, '`username` = ? and `AppId` = ? and `PartnerId` = ?', $bind);
	}
	/**
	 * 查询单个字段
	 * @param $AreaId
	 * @param $fields
	 * @return array
	 */
	public function getOne($wherePartnerPermission, $field='*')
	{
		$sql = "SELECT $field FROM {$this->table} WHERE 1 ".$wherePartnerPermission;
		return $this->db->getRow($sql);	
	}

	/**
	 * 获取测试帐号
	 * @param $whereParterPermission 用户权限组
	 * @param $AppId 游戏
	 * @return array
	 */
	public function getTestUser($wherePartnerPermission,$AppId, $fields = "*")
	{
		$where0 = " or (AppId = 0 and PartnerId = 0)";

		if($AppId>0)
		{
			$whereApp = " or (AppId = $AppId and PartnerId = 0)";
		}
		else
		{
			$whereApp = " or (AppId = 0 or PartnerId = 0) ";	
		}
		$sql = "SELECT $fields FROM $this->table where $wherePartnerPermission $whereApp $where0 ORDER BY username,AppId,PartnerId";
		$totalUser = $this->db->getAll($sql);
		return $totalUser;
	}
	/**
	 * 获取全部
	 * @param $fields
	 * @return array
	 */
	public function getAllTestUser($fields="*")
	{
		$sql = "SELECT $fields FROM $this->table where 1 ORDER BY AppId,PartnerId,username";
		return $this->db->getAll($sql);
	}
}