<?php
/**
 * socket类型
 * @author Chen<cxd032404@hotmail.com>
 * $Id: SocketType.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class Config_SocketType extends Base_Widget
{
	/**
	 * server表
	 * @var string
	 */
	protected $table = "socket_type";

	/**
	 * 获取单条记录
	 * @param integer $Type
	 * @param string $fields
	 * @return array
	 */
	public function getRow($Type, $fields = '*')
	{
		return $this->db->selectRow($this->getDbTable(), $fields, '`Type` = ?', $Type);
	}
	
	/**
	 * 获取单个字段
	 * @param integer $Type
	 * @param string $field
	 * @return string
	 */
	public function getOne($Type, $field)
	{
		return $this->db->selectOne($this->getDbTable(), $field, '`Type` = ?', $Type);
	}
	
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert($this->getDbTable(), $bind);
	}
    
    /**
	 * 更新单条类型数据
	 * @param integer $Type
	 * @param array $bind
	 * @return boolean
	 */
	public function update($Type, array $bind)
	{
		return $this->db->update($this->getDbTable(), $bind, '`Type` = ?', $Type);
	}

	/**
	 * 删除
	 * @param integer $Type
	 * @return boolean
	 */
	public function delete($Type)
	{
		return $this->db->delete($this->getDbTable(), '`Type` = ?', $Type);
	}

	/**
	 * 获取所有类型
	 * @param string $fields
	 * @return array
	 */
	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY Type ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllQueue[$value['Type']] = $value;	
			}	
		}
		return $AllQueue;
	}

    public function reBuildSocketTypeConfig($fields = "*")
	{
		$AllSocketType = $this->getAll($fields);
		$file_path = "../../etc/";
		$file_name = "SocketType.php";
		$var = var_export($AllSocketType,true);
		$text ='<?php $AllSocketType='.$var.'; return $AllSocketType;?>';		
		file_put_contents($file_path.$file_name,$text);
	}
}
