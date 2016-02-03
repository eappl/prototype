<?php
/**
 * 用户平台币操作
 * $Id: Widget_Coin.php 陈晓东$
 * @author chenxd
 *
 */

class Widget_Coin extends Base_Widget
{
	protected $table = 'p_clb';

	protected $minCoin = 1;

	protected $maxCoin = 10000;
	
	/**
	 * 添加平台币
	 * @param array $bind
	 * @return boolean
	 */
	public function addCoin($username, $coin)
	{
		$coin = floor($coin);

		if ($coin < $this->minCoin || $coin > $this->maxCoin) {
			return false;
		}

		$table = $this->getDbTable();

		$sql = "UPDATE ".$this->getDbTable()." SET `clb` = `clb` + $coin, `credit` = `credit` + $coin WHERE `user_account` = ? ";
		$result = $this->db->query($sql, $username);
		if ($result !== 1) {
			$sql = "INSERT INTO ".$this->getDbTable()." (`user_account`, `clb`, `credit`) VALUES (?, $coin, $coin);";
			$result = $this->db->query($sql, $username);
		}

		return $result;
	}

	/**
	 * 扣除平台币
	 * @param array $bind
	 * @return boolean
	 */
	public function reduceCoin($username, $coin)
	{
		$coin = floor($coin);

		if ($coin < $this->minCoin || $coin > $this->maxCoin) {
			return false;
		}

		$table = $this->getDbTable();

		$sql = "UPDATE $table SET `clb`=`clb` - $coin WHERE `user_account` = ? AND `clb` >= $coin LIMIT 1;";
		$result = $this->db->query($sql, $username);

		return $result;
	}
}