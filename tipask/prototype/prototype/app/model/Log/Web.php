<?php

class Log_Web extends Base_Widget
{
	protected $table = 'config_logs_web';
	
	protected $logArr = array();
	
	public static function factory()
	{
		return new self();
	}

	public function insert()
	{
		$result = Base_Common::pputHttpSQS($this->getDbTable(),$this->logArr);
		if (!$result) {
			$fp = fopen(Base_Common::$config['vars_dir'] . 'error.log', 'a+');
			fwrite($fp, var_export($this->logArr, true));
			fclose($fp);
		}
	}

	public function push($key, $value)
	{
		if (empty($value)) {
			$value = '';
		}
		
		$this->logArr[$key] = $value;
		
		return $this;
	}
	
	public function getAll($sql, $params = array())
	{
		return $this->db->getAll($sql, $params, false);
	}

	public function getOne($sql, $params = array())
	{
		return $this->db->getOne($sql, $params, false);
	}
	
}
