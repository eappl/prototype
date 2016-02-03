<?php
/**
 * PDO操作 mysql
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Db.php 15499 2014-12-18 09:16:24Z 334746 $
 */


class Base_Db
{
    /**
     * dsn
     * @var string
     */
	protected $dsn = '';

	/**
	 * 读写连接
	 * @var resource
	 */
	protected $write_conn = null;

	/**
	 * 读连接
	 * @var resource
	 */
	protected $read_conn = null;

	/**
	 * 读写连接配置
	 * @var array
	 */
	protected $write_conf = array();

	/**
	 * 读连接配置
	 * @var array
	 */
	protected $read_conf = array();

	protected $fetch_mode = PDO::FETCH_ASSOC;

	/**
	 * @var boolean
	 */
	protected $is_persistent = false;

	/**
	 * @var boolean
	 */
	protected $is_master = true;

	protected $last_sql = array('sql' => '', 'params' => '');

	/**
	 * 错误信息
	 * @var string
	 */
	protected $error;

	/**
	 * 错误代码
	 * @var string
	 */
	protected $errno;

	/**
	 * @var PDO object
	 */
	protected $dbh = null;

	/**
	 * 启动事务标识，以便支持程序中的事务嵌套
	 * @var integer
	 */
	protected $inTransaction = 0;

	public function setFetchMode($fetch_mode)
	{
		$this->fetch_mode = $fetch_mode;
	}

	public function setWriteConf(array $conf)
	{
		$this->write_conf = $conf;
		return $this;
	}

	public function setReadConf (array $conf)
	{
		$this->read_conf = $conf;
		return $this;
	}

	public function setIsMaster($is_master)
	{
		$this->is_master = $is_master;
		return $this;
	}

	public function setIsPersistent($is_persistent)
	{
		$this->is_persistent = $is_persistent;
		return $this;
	}

	/**
	 * 可写连接
	 */
	protected function getWriteConn()
	{
		/**
		 * 唯一实例
		 */
		if ($this->write_conn && ($this->write_conn instanceof PDO)) {
			return $this->write_conn;
		}

		$db = $this->connect($this->write_conf);
		if ($db && ($db instanceof PDO)) {
			$this->write_conn = $db;
			return $this->write_conn;
		}

		return false;
	}

	/**
	 * 只读连接
	 */
	protected function getReadConn()
	{
		/**
		 * 唯一实例
		 */
		if ($this->read_conn && ($this->read_conn instanceof PDO)) {
			return $this->read_conn;
		}

		$arrHost = explode("|", $this->read_conf['host']);
		if (!is_array($arrHost) || empty($arrHost)) {
			return $this->getWriteConn();
		}

		shuffle($arrHost);

		foreach ($arrHost as $host) {
			$this->read_conf['host'] = $host;
			$db = $this->connect($this->read_conf);
			if ($db && ($db instanceof PDO)) {
				$this->read_conn = $db;
				return $this->read_conn;
			}
		}

		return false;
	}

	protected function connect($dsn = null, $is_persistent = false)
	{
		try {
			$this->dsn = 'mysql:host=' . $dsn['host'] . ';port=' . $dsn['port'] . ';dbname=' . $dsn['database'];
			$params = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8');
			if ($this->is_persistent || $is_persistent) {
				$params[PDO::ATTR_PERSISTENT] = true;
			}
			$dbh = new PDO($this->dsn, $dsn['user'], $dsn['password'], $params);
		} catch (PDOException $e) {
			throw new Base_Db_Exception($this->dsn . ' connect failed');
		}

		return $dbh;
	}

	/**
	 * 连接数据库
	 * @param boolean $is_master
	 */
	protected function getConn($is_master = null)
	{
		if (null === $is_master) {
			$is_master = $this->is_master;
		}

		$this->dbh = $is_master ? $this->getWriteConn() : $this->getReadConn();
	}

	protected function autoExecute($sql, $params = array(), $is_master = true)
	{
		$start_time = microtime(true);

		if (!is_array($params)) {
			$params = array($params);
		}

		$this->last_sql = array('sql' => $sql, 'params' => $params);
		$this->getConn($is_master);

		$sth = $this->dbh->prepare($sql); //PDOStatement object

		$sth->execute($params);
        if ($sth->errorCode() != '0000') {
            $this->errno = $sth->errorCode();
            $this->error = implode(',', $sth->errorInfo());
            $this->error .= "\n\n" . $this->getLogSql($sql, $params);
            print_R($this->error);
            }
		$this->log($sql, $params, microtime(true) - $start_time);

		return $sth;
	}

	public function query($sql, $params = array(), $is_master = true)
	{
		$sth = $this->autoExecute($sql, $params, $is_master);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			$rows = $sth->rowCount();
			return $rows > 0 ? $rows : true;
		} else {
			return false;
		}
	}

	public function getAll($sql, $params = array(), $is_master = false)
	{
		$sth = $this->autoExecute($sql, $params, $is_master);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			return $sth->fetchAll($this->fetch_mode);
		} else {
			return array();
		}
	}

	public function select($table, $fields = '*', $where = '', $params = array(), $limit = 0, $offset = 0, $is_master = false)
	{
		$table = str_replace('.', '`.`', $table);
		$where = trim($where);
		$where = ('' == $where ? '' : " WHERE $where ");
		$sql = "SELECT $fields FROM `".$table."` $where";

		$limit = max(0, intval($limit));
		if (!empty($limit)) {
			$sql .= " LIMIT $limit";

			$offset = max(0, intval($offset));
			if ($offset > 0) {
				$sql .= " OFFSET $offset";
			}
		}

		return $this->getAll($sql, $params, $is_master);
	}

	public function getCol($sql, $params = array(), $is_master = false)
	{
		$sth = $this->autoExecute($sql, $params, $is_master);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			return $sth->fetchAll(PDO::FETCH_COLUMN, 0);
		} else {
			return array();
		}
	}

	/**
	 * 获取key=>value模式记录
	 * @param string $sql
	 * @param array $params
	 * @param boolean $is_master
	 * @return array
	 */
	public function getPairs($sql, $params = array(), $is_master = false)
	{
		$sth = $this->autoExecute($sql, $params, $is_master);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			$data = array();
			while ($row = $sth->fetch(PDO::FETCH_NUM)) {
				$data[$row[0]] = $row[1];
			}
		}

		return $data;
	}

	/**
	 * 获取单个字段
	 *
	 * @param string $sql
	 * @param mixed $params
	 * @param boolean $is_master
	 * @return mixed
	 */
	public function getOne($sql, $params = array(), $is_master = false)
	{
		$sth = $this->autoExecute($sql, $params, $is_master);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			return $sth->fetchColumn();
		} else {
			return null;
		}
	}

	public function selectOne($table, $field, $where, $params = array(), $is_master = false)
	{
		$table = str_replace('.', '`.`', $table);
		$where = trim($where);
		$where = ('' == $where ? '' : " WHERE $where ");
		$sql = "SELECT $field FROM `".$table."` $where";

		return $this->getOne($sql, $params, $is_master);
	}

	/**
	 * 获取单条记录
	 *
	 * @param string $sql
	 * @param mixed $params
	 * @param boolean $is_master
	 * @return array
	 */
	public function getRow($sql, $params = array(), $is_master = false)
	{
		$sth = $this->autoExecute($sql, $params, $is_master);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			return $sth->fetch($this->fetch_mode);
		} else {
			return array();
		}
	}

	public function selectRow($table, $fields = '*', $where, $params = array(), $is_master = false)
	{
		$table = str_replace('.', '`.`', $table);
		$where = trim($where);
		$where = ('' == $where ? '' : " WHERE $where ");
		$sql = "SELECT $fields FROM `".$table."` $where LIMIT 1;";

		return $this->getRow($sql, $params, $is_master);
	}

	/**
	 * 删除指定的记录
	 *
	 * @param string $table
	 * @param string $where
	 * @param mixed $params
	 * @return mixed
	 */
	public function delete($table, $where, $params = array())
	{
		/**
		 * 禁止删除所有记录
		 */
		$table = str_replace('.', '`.`', $table);
		if (empty($where)) {
			return false;
		}

		if (!is_array($params)) {
		    $params = array($params);
		}

		$sql = 'DELETE FROM `' . $table . '` WHERE ' . $where;
		return $this->query($sql, $params, true);
	}

	/**
	 * INSERT
	 *
	 * @param string $table
	 * @param array $bind
	 * @return integer
	 */
	public function insert($table, array $bind, $keywords = '')
	{
		$table = str_replace('.', '`.`', $table);
		$cols = $vals = array();
		foreach ($bind as $col => $val) {
			$cols[] = $col;
			$vals[] = '?';
		}

		$keywords = strtoupper($keywords);
		if (!in_array($keywords, array('DELAYED', 'IGNORE'))) {
			$keywords = '';
		}

		$sql = 'INSERT ' . $keywords . ' INTO `' . $table . '`'
			. '(`' . implode('`, `', $cols) . '`)'
			. 'VALUES (' . implode(', ', $vals) . ');';

		$sth = $this->autoExecute($sql, array_values($bind), true);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			$result = $this->dbh->lastInsertId();
			if ($result != '0') {
				return $result;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * INSERT
	 *
	 * @param string $table
	 * @param array $bind
	 * @return integer
	 */
	public function insert_update($table, array $bind, array $update,$keywords = '')
	{
		$table = str_replace('.', '`.`', $table);
		$cols = $vals = array();
		foreach ($bind as $col => $val) {
			$cols[] = $col;
			$vals[] = '?';
		}

		foreach ($update as $col => $val) {
			if(is_numeric(stripos($val,"_")))
			{
			    $update_cols[] = $col."=".substr($val,1);
			    unset($update[$col]);
		    }
		    else
		    {
			    $update_cols[] = $col."=?";
            }
		}

		$keywords = strtoupper($keywords);
		if (!in_array($keywords, array('DELAYED', 'IGNORE'))) {
			$keywords = '';
		}

		$sql = 'INSERT ' . $keywords . ' INTO `' . $table . '`'
			. '(`' . implode('`, `', $cols) . '`)'
			. ' VALUES (' . implode(', ', $vals) . ') ON DUPLICATE KEY UPDATE '.implode(', ',$update_cols).";";
		$t1 = array_values($bind);
		$t2 = array_values($update);
//		foreach($t2 as $k => $value)
//		{
//			$t = count($t1)+1+$k;
//			$t3[$t] = $value;
//		}

		$new_bind = array_values(array_merge($t1,$t2));
		$sth = $this->autoExecute($sql, $new_bind, true);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			$result = $this->dbh->lastInsertId();
			if ($result != '0') {
				return $result;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function replace($table, array $bind)
	{
		$table = str_replace('.', '`.`', $table);
		$cols = $vals = array();
		foreach ($bind as $col => $val) {
			$cols[] = $col;
			$vals[] = '?';
		}
		$sql = 'REPLACE INTO `' . $table . '`'
			. '(`' . implode('`, `', $cols) . '`)'
			. 'VALUES (' . implode(', ', $vals) . ');';
		$sth = $this->autoExecute($sql, array_values($bind), true);
		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			$result = $this->dbh->lastInsertId();
			if ($result != '0') {
				return $result;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * 多条记录INSERT INTO
	 *
	 * @param string $table
	 * @param array $rows
	 * @return boolean
	 */
	public function inserts($table, array $rows, $delayed = false)
	{
		$table = str_replace('.', '`.`', $table);
		if (empty($rows)) {
			return true;
		}

		$cols = $vals = array();
		foreach ($rows as $n => $row) {
			$arr = array();
			foreach ($row as $col => $val) {
				if ($n == 0) {
					$cols[] = $col;
				}
				$arr[] = $this->dbh->quote($val);
			}
			$vals[] = '(' . implode(', ', $arr) . ')';
		}
		$delayed = $delayed ? 'DELAYED' : '';
		$sql = 'INSERT ' . $delayed . ' INTO `' . $table . '`(`' . implode('`, `', $cols) . '`) VALUES' . implode(', ', $vals);
		$sth = $this->autoExecute($sql, array(), true);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			return true;
		} else {
			return false;
		}
	}

	public function replaces($table, array $rows)
	{
		$table = str_replace('.', '`.`', $table);
		if (empty($rows)) {
			return true;
		}

		$cols = $vals = array();
		foreach ($rows as $n => $row) {
			$arr = array();
			foreach ($row as $col => $val) {
				if ($n == 0) {
					$cols[] = $col;
				}
				$arr[] = $this->dbh->quote($val);
			}
			$vals[] = '(' . implode(', ', $arr) . ')';
		}
		$sql = 'REPLACE INTO `' . $table . '`(`' . implode('`, `', $cols) . '`) VALUES' . implode(', ', $vals);

		$sth = $this->autoExecute($sql, array(), true);

		$errorCode = $sth->errorCode();
		if ($errorCode == '0000') {
			return true;
		} else {
			return false;
		}
	}

	public function update($table, $bind, $where = '', $params = array())
	{
		$table = str_replace('.', '`.`', $table);
		$where = trim($where);
		$sets = $vals = array();
		foreach ($bind as $col => $val)
		{
			if(is_numeric(stripos($val,"_"))  && stripos($val,"_") == 0)
			{
			    $sets[] = $col."=".substr($val,1);
			    unset($update[$col]);
		    }
		    else
		    {
    			$sets[] = '`' . $col . '` = ?';
    			$vals[] = $val;
            }
		}

		$sql = "UPDATE `{$table}` SET " . implode(', ', $sets);

		if ($where) {
			if (!is_array($params)) {
		        $params = array($params);
	    	}
		    $vals = array_merge($vals, $params);
		    $sql .= ' WHERE ' . $where;
		}
		return $this->query($sql, $vals, true);
	}

	public function limitQuery($sql, $params = array(), $limit = 0, $offset = 0, $is_master = false)
	{
		$limit = max(0, intval($limit));
		if (!empty($limit)) {
			$sql .= " LIMIT $limit";

			$offset = max(0, intval($offset));
			if ($offset > 0) {
				$sql .= " OFFSET $offset";
			}
		}
		return $this->getAll($sql, $params, $is_master);
	}

	public function lastInsertId()
	{
		return $this->dbh->lastInsertId();
	}

	protected static function _quote($value)
	{
		if (is_int($value)) {
			return $value;
		} else if (is_float($value)) {
			return sprintf('%F', $value);
		} else {
			return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
		}
	}

	/**
	 * PDO beginTransaction method
	 *
	 * @return boolean
	 */
	public function begin()
	{
		$begin = false;
		if (0 == $this->inTransaction) {
			$this->getConn(true);
			$begin = $this->dbh->beginTransaction();
		}

		$this->inTransaction++;

		return $begin;
	}

	/**
	 * PDO commit method
	 *
	 * @return boolean
	 */
	public function commit()
	{
		$commit = false;
		if (1 == $this->inTransaction) {
			$this->getConn(true);
			$commit = $this->dbh->commit();
		}

		$this->inTransaction--;

		return $commit;
	}

	/**
	 * PDO rollBack method
	 *
	 * @return boolean
	 */
	public function rollBack()
	{
		$rollback = false;
		if (1 == $this->inTransaction) {
			$this->getConn(true);
			$rollback = $this->dbh->rollBack();
		}

		$this->inTransaction--;

		return $rollback;
	}

	public function getLogSql($sql = '', $params = '')
	{
		if ($sql == '') {
			$sql = $this->last_sql['sql'];
			$params = $this->last_sql['params'];
		}

		return $sql . (empty($params) ? '' : ' [' . join(',', $params) . ']');
	}

	public function log($sql, $params, $time)
	{
/*
		echo "\n<!--";
		echo $time , "\n";
		echo $sql, "\n";
		print_r($params);
		echo "-->\n";
*/
        /*
		if ($time > 0.1) {
			$log = "\n#" . date('Y-m-d H:i:s') . "==\n";
			$log .= $time . "\n" . $sql . "\n";
			$log  .= var_export($params,true);
			$log .= "\n#";
			$logFile = Base_Common::$config['var_dir'] . 'log/sql_' . date('Ymd') . '_slow.log';
			$fp = fopen($logFile, 'a');
			fwrite($fp,$log);
			fclose($fp);
		}
		*/
	}
	public function checkTableExist($TableName)
	{
		$t = explode(".",$TableName);
		$db = $t[0];
		$table = $t[1];
		$this->query("use $db");
		$table_list = $this->getAll("show tables where Tables_in_$db like '$table'");
		return count($table_list);
	}

	public function getErrno()
	{
	    return $this->errno;
	}

	public function getError()
	{
	    return $this->error;
	}

}
