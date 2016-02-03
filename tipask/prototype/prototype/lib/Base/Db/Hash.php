<?php
/**
 * 数据库管理
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Hash.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Db_Hash
{

	/**
	 * db config
	 * @var array
	 */
	protected $dbConf = array();

	/**
	 * table config
	 * @var array
	 */
	protected $tableConf = array();

	/**
	 * Base_Db object
	 * @var array
	 */
	protected $oDbArr = array();

	protected static $instance = null;

	private function __construct()
	{
		$this->dbConf = (@include Base_Common::$config['database_file']);
		$this->tableConf = (@include Base_Common::$config['table_file']);
	}

	/**
	 * 唯一实例
	 *
	 * @return Base_Db_Hash
	 */
	public static function getInstance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getHashTable($table, $db = null, $key = null)
	{
		if (($key === null || $this->tableConf[$table]['num'] < 16) &&( $db == null)) 
		{
			return $this->dbConf[$this->tableConf[$table]['db']][0]['database'] . '.' . $table . '';
		} 
		elseif (($key === null || $this->tableConf[$table]['num'] < 16) &&( $db != null)) 
		{
			return $db . '.' . $table . '';
		}else 
		{
			return '' . $this->dbConf[$this->tableConf[$table]['db']][0]['database'] . '.'
				. $table . '_'
				. hexdec(substr(md5($key), 0, log($this->tableConf[$table]['num'], 16)))
			 	. '';
		}
	}

	/**
	 * 初始化数据库配置
	 * @param string $table
	 * @param boolean $isMaster
	 * @param boolean $isPersistent
	 * @return Base_Db
	 */
	public function prepare($table, $isMaster = false, $isPersistent = false)
	{

		$t = explode(".",$table);
		$table = $t[count($t)-1];
		if (!isset($this->tableConf[$table])) {
					echo $table."<br>";
		print_R($this->tableConf);
			die($table . ' to database config error');
		}

		$dbKey = $this->tableConf[$table]['db'];
		$db_count = count($this->dbConf[$dbKey]);
		$writeConf = array(
			'host' => $this->dbConf[$dbKey][0]['host'],
			'user' => $this->dbConf[$dbKey][0]['user'],
			'password' => $this->dbConf[$dbKey][0]['password'],
			'port' => empty($this->dbConf[$dbKey][0]['port']) ? 3306 : $this->dbConf[$dbKey][0]['port'],
			'database' => $this->dbConf[$dbKey][0]['database']
		);
		//if (isset($this->dbConf[$dbKey][1]))
		if ($db_count>=2) 
		{
			$rand = rand(1,$db_count-1);
			$readConf = array(
				'host' => $this->dbConf[$dbKey][$rand]['host'],
				'user' => $this->dbConf[$dbKey][$rand]['user'],
				'password' => $this->dbConf[$dbKey][$rand]['password'],
				'port' => empty($this->dbConf[$dbKey][$rand]['port']) ? 3306 : $this->dbConf[$dbKey][$rand]['port'],
				'database' => $this->dbConf[$dbKey][$rand]['database']
			);
		} else {
			$readConf = $writeConf;
		}
		$key = md5($writeConf['host'] . ':' . $writeConf['port'] . ';' . $writeConf['user'] . ':' . $writeConf['password']);

		if (isset($this->oDbArr[$key]) && is_object($this->oDbArr[$key])) {
			if ($isMaster) {
				$this->oDbArr[$key]->setIsMaster($isMaster);
			}
		} else {
			$oDb = new Base_Db();
			$oDb->setReadConf($readConf);
			$oDb->setWriteConf($writeConf);
			$oDb->setIsMaster($isMaster);
			$isPersistent = ($isPersistent || $this->dbConf['isPersistent']);
			$oDb->setIsPersistent($isPersistent);
			$this->oDbArr[$key] = $oDb;
		}
		return $this->oDbArr[$key];
		
	}

}
