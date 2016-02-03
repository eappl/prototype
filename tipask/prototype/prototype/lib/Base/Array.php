<?php
/**
 * 数组处理
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Array.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Array
{

	/**
	 * 从数组中提取一个值作为键值， 一个值作为值组成一个新的数组
	 * @param array $value
	 * @param string $v
	 * @param string $k
	 * @return array
	 */
	public static function flatten(array $value, $v, $k = null)
	{
		$result = array();

		if (!empty($value)) {
			foreach ($value as $val) {
				if (is_array($val)&& isset($val[$v])) {
					if ($k !== null && isset($val[$k])) {
						$result[$val[$k]] = $val[$v];
					} else if ($k === null) {
						$result[] = $val[$v];
					} else {
						break;
					}
				} else {
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * 从数组中提取一个指定键的值作为新数组的键
	 * @param array $value
	 * @param string $k
	 * @return array
	 */
	public static function refine(array $value, $k)
	{
		$result = array();

		if (!empty($value)) {
			foreach ($value as $val) {
				if (is_array($val)&& isset($val[$k])) {
					$result[$val[$k]] = $val;
				} else {
					break;
				}
			}
		}

		return $result;
	}

}
