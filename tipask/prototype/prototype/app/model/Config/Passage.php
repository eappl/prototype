<?php
/**
 * 支付渠道管理
 * $Id: Passage.php 15195 2014-07-23 07:18:26Z 334746 $
 * @author chenxd
 *
 */


class Config_Passage extends Base_Widget
{
	/**
	 * 支付渠道表
	 * @var string
	 */
	protected $table = 'config_passage';
	protected $table_tenpay = "tenpaylist";
	protected $table_alipay = "alipaylist";
  	protected $table_91ka = "ka91list";



	/**
	 * 获取全部支付渠道
	 * @param string $fields
	 * @return array
	 */
	
	public function getAll($fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process ORDER BY `passage_id`,`kind` ASC, `sort` DESC";
		$return = $this->db->getAll($sql);
		foreach($return as $key => $value)
		{
			$AllPassage[$value['passage_id']] = $value;	
		}
		return $AllPassage;
	}

	/**
	 * 根据ID获取支付渠道信息
	 * @param integer $passage_id
	 * @param string $fields
	 * @return array
	 */
	public function getRow($passage_id, $fields = '*')
	{
		return $this->db->selectRow($this->getDbTable(), $fields, '`passage_id` = ?', $passage_id);
	}

	/**
	 * 根据渠道标识获取支付渠道信息
	 * @param string $passage
	 * @param string $fields
	 * @return array
	 */
	public function getByPassage($passage, $fields = '*')
	{
		return $this->db->selectRow($this->getDbTable(), $fields, '`passage` = ?', $passage);
	}

	/**
	 * 根据分类获取支付渠道
	 * @param string $kind 银行分类标识
	 * @return array
	 */
	public function getByKind($kind, $fields = '*')
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " WHERE `kind` = ? ";
		return $this->db->getAll($sql, $kind);
	}

	/**
	 * 添加支付渠道
	 * @param array $params
	 * @return boolean
	 */
	public function insert(array $params)
	{
		$insertStruct = array (
			'passage' => $params['passage'],
			'name' => $params['name'],
			'passage_rate' => $params['passage_rate'],
			'finance_rate' => $params['finance_rate'],
			'kind' => $params['kind'],
			'sort' => empty($params['sort']) ? 80 : $params['sort'],
			'weight' => empty($params['weight']) ? 1 : $params['weight'],
		);
		$result = $this->db->insert($this->getDbTable(), $insertStruct);
		if($result)	{
			return $result;
		} else {
			$this->errno = 99;
			$this->error = $this->db->getError();
			return false;
		}
	}

	/**
	 * 修改支付渠道
	 * @param string $passage_id 支付渠道ID
	 * @param array $params
	 * @return boolean
	 */
	public function update($passage_id, array $params)
	{
		$passage_id = intval($passage_id);
		
		$preUpdateStruct = array (
				'name' => $params['name'],
				'passage_rate' => $params['passage_rate'],
				'finance_rate' => $params['finance_rate'],
				'kind' => $params['kind'],
				'sort' => empty($params['sort']) ? 80 : $params['sort'],
				'weight' => empty($params['weight']) ? 1 : $params['weight'],
				'StageUrl' => $params['StageUrl'],
				'StagePartnerId' => $params['StagePartnerId'],
				'StageSecureCode' => $params['StageSecureCode'],
			);
			
		$updateStruct = array();
		foreach($params as $key=>$val){
			if(array_key_exists($key,$preUpdateStruct)){
				$updateStruct[$key] = $preUpdateStruct[$key];
			}
			
		}
		$result = $this->db->update($this->getDbTable(), $updateStruct, '`passage_id` = ?', $passage_id);
		if ($result) {
			return $result;
		} else {
			$this->errno = 98;
			$this->error = $this->db->getError();
			return false;
		}
	}

	/**
	 * 删除支付渠道
	 * @param integer $passage_id 支付渠道ID
	 * @return boolean
	 */
	public function delete($passage_id)
	{
		return $this->db->delete($this->getDbTable(), '`passage_id` = ?', $passage_id);

//		$oPassageBank = new Config_PassageBank();
//		$res_passagebank = $oPassageBank->deleteByPassage($passage_id);
//
//		if ($res_passage && $res_passagebank) {
//			$this->db->commit();
//
//			return true;
//		} else {
//			$this->db->rollBack();
//
//			$this->errno = 97;
//			$this->error = $this->db->getError();
//
//			return false;
//		}
	}

	/**
	 * 根据渠道ID获取渠道信息
	 * @param string $passage_id
	 * @param string $field
	 * @return mix
	 */
	public function getOne($passage_id, $field)
	{
		return $this->db->selectOne($this->getDbTable(), $field, '`passage_id` = ?', $passage_id);
	}

	/**
	 * 根据标识获取单个字段
	 * @param string $passage
	 * @param string $field
	 * @return string
	 */
	public function getOneByPassage($passage, $field)
	{
		return $this->db->selectOne($this->getDbTable(), $field, '`passage` = ?', $passage);
	}

	/**
	 * 检测渠道标识是否存在
	 * @param string $passage
	 * @return boolean
	 */
	public function passageExists($passage)
	{
		$passage_id = $this->getOneByPassage($passage, 'passage_id');
		return !empty($passage_id);
	}
	
	public function getTenPayList($Start,$Count,$IsB2b)
	{
		$whereB2b = $IsB2b?" and IsB2b = $IsB2b ":"";
		$limit = $Count? " limit $Start , $Count":"";
		$table_to_process = Base_Widget::getDbTable($this->table_tenpay);
		$sql = "select * from $table_to_process where 1 ".$whereB2b." order by `Index`,`IsB2b`".$limit;
		return $this->db->getAll($sql);			
	}
	public function getAliPayList($Start,$Count,$IsB2b)
	{
		$whereB2b = $IsB2b?" and IsB2b = $IsB2b ":"";
		$limit = $Count? " limit $Start , $Count":"";
		$table_to_process = Base_Widget::getDbTable($this->table_alipay);
		$sql = "select * from $table_to_process where 1 ".$whereB2b." order by `Index`,`IsB2b`".$limit;
		return $this->db->getAll($sql);			
	}
 	public function getKa91List($Start,$Count,$IsB2b)
	{
		$whereB2b = $IsB2b?" and IsB2b = $IsB2b ":"";
		$limit = $Count? " limit $Start , $Count":"";
		$table_to_process = Base_Widget::getDbTable($this->table_91ka);
		$sql = "select * from $table_to_process where 1 ".$whereB2b." order by `Index`,`IsB2b`".$limit;
		return $this->db->getAll($sql);			
	}

}
