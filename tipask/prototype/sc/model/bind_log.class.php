<?php
/**
 * 生成后台个人设置setting 中的图片
 */
!defined('IN_TIPASK') && exit('Access Denied');

class bind_logmodel {

    var $base;
	var $table_bindLog = 'bind_log';
	var $table_orderToPorcess = 'order_log';
	var $table_order = 'order_log_e';
	var $table_operator = 'ask_operator';
	var $pdo = null;
	
    function bind_logmodel(&$base)
	{
        $this->base = $base;
	    $this->pdo = $base->init_pdo($this->table_bindLog);
    }
/**
* $bindLogArr 一维数组
* author登陆用户名
* scid 绑定客服id
* time 操作时间十位整形
*/
    function bindUnbindOperator($bindLogArr)
	{
		$bindLogInfo = $this->getBindLogInfo($bindLogArr['author']);
		
		if(!empty($bindLogInfo))
		{
			// 未解绑
			if($bindLogInfo['unbind_time'] == 0)
			{
				if( $bindLogInfo['scid'] == $bindLogArr['scid'])
				{
					// 绑定同一个人不操作
					return true;
				}
				else
				{
					$this->pdo->begin(); 	
					
					//解绑记录(用户)
					$unbindResult = $this->updateBindLog($bindLogArr,$bindLogInfo['id']); //unbind
					//绑定记录(用户)
					$bindResultAuthor  = $this->addBindLogAuthor($bindLogArr); 	//bind
					//绑定记录(日期)
					$LogArr = array('author'=>$bindLogArr['author'],'scid'=>$bindLogArr['scid'],'time'=>$bindLogArr['bind_time'],'bind_type'=>'bind');
					$bindResultDate  = $this->addBindLogDate($LogArr); 	//bind
					//绑定记录(用户)
					$LogArr = array('author'=>$bindLogArr['author'],'scid'=>$bindLogInfo['scid'],'time'=>$bindLogArr['bind_time'],'bind_type'=>'unbind');
					$unbindResultDate  = $this->addBindLogDate($LogArr); 	//bind
					if($unbindResult>0 && $bindResultAuthor>0 && $bindResultDate>0 && $unbindResultDate>0 )
					{
						$this->pdo->commit();
						return true;
					}
					else
					{
						$this->pdo->rollBack();
						return false;
					}
				}
			}
			else
			{
				$this->pdo->begin();
				//绑定记录(用户)
				$bindResultAuthor  = $this->addBindLogAuthor($bindLogArr); 	//bind				
				//绑定记录(日期)
				$LogArr = array('author'=>$bindLogArr['author'],'scid'=>$bindLogArr['scid'],'time'=>$bindLogArr['bind_time'],'bind_type'=>'bind');
				$bindResultDate  = $this->addBindLogDate($LogArr); 	//bind
				if($bindResultAuthor>0 && $bindResultDate>0)
				{
					$this->pdo->commit();
					return true;
				}
				else
				{
					$this->pdo->rollBack();
					return false;
				}
			}
		}
		else
		{
			$this->pdo->begin();
			//绑定记录(用户)
			$bindResultAuthor  = $this->addBindLogAuthor($bindLogArr); 	//bind				
			//绑定记录(日期)
			$LogArr = array('author'=>$bindLogArr['author'],'scid'=>$bindLogArr['scid'],'time'=>$bindLogArr['bind_time'],'bind_type'=>'bind');
			$bindResultDate  = $this->addBindLogDate($LogArr); 	//bind
			if($bindResultAuthor>0 && $bindResultDate>0)
			{
				$this->pdo->commit();
				return true;
			}
			else
			{
				$this->pdo->rollBack();
				return false;
			}
		}
	}		
			
	/**
	* $author 登陆用户名
	*/
	function getBindLogInfo($author)
	{
		$table_name = $this->base->getDbTable($this->table_bindLog);
		$bindLogSuffix = '_author'.$this->base->getSuffixTable($author);
		$bindLogTableName  = $this->pdo->createLogTable($table_name, $bindLogSuffix);  // 取新建的表名
		
		$sql = "select id,author,scid,bind_time,unbind_time from $bindLogTableName where author='$author' ORDER BY id DESC  limit 1";
		return $this->pdo->getRow($sql);
		
	}
/**
* $bindLogArr 一维数组
* author登陆用户名
* scid 绑定客服id
* time 操作时间十位整形
*/
	function addBindLogAuthor($dataArr)
	{
		$table_name = $this->base->getDbTable($this->table_bindLog);
		$bindLogSuffix = '_author'.$this->base->getSuffixTable($dataArr['author']);
		$bindLogTableName  = $this->pdo->createLogTable($table_name, $bindLogSuffix);  // 取新建的表名
		return $this->pdo->insert($bindLogTableName,$dataArr);		
	}
	function addBindLogDate($dataArr)
	{
		$table_name = $this->base->getDbTable($this->table_bindLog);
		$bindLogSuffix = '_date_'.date("Ym",$dataArr['time']);
		$bindLogTableName  = $this->pdo->createLogTable($table_name, $bindLogSuffix);  // 取新建的表名
		return $this->pdo->insert($bindLogTableName,$dataArr);		
	}
/**
* $bindLogArr 一维数组
* author登陆用户名
* scid 绑定客服id
* bind_time 操作时间十位整形
*/
	function updateBindLog($dataArr,$id)
	{
		$table_name = $this->base->getDbTable($this->table_bindLog);
		$bindLogSuffix = '_author'.$this->base->getSuffixTable($dataArr['author']);
		$bindLogTableName  = $this->pdo->createLogTable($table_name, $bindLogSuffix);  // 取新建的表名	
		return $this->pdo->update($bindLogTableName, array('unbind_time'=>$dataArr['bind_time']), '`id` = ?', $id);
		
	}
	function getBindStatus($author,$scid,$time)
	{
		$table_name = $this->base->getDbTable($this->table_bindLog);
		$bindLogSuffix = '_author'.$this->base->getSuffixTable($author);
		$bindLogTableName  = $this->pdo->createLogTable($table_name, $bindLogSuffix);  // 取新建的表名
		$sql = "select * from ".$bindLogTableName." where scid=$scid and author = '".$author."' and bind_time<=".$time." and (unbind_time>=".$time." or unbind_time =0)";
		return $this->pdo->getRow($sql);
		
	}
	function getOrderLog($count = 1000)
	{
		$table_name = $this->base->getDbTable($this->table_orderToPorcess);
		$sql = "select * from ".$table_name." order by datakey limit ".$count;
		return $this->pdo->getAll($sql);
	}
	function insertOrderLogAuthor($dataArr)
	{
		$table_name = $this->base->getDbTable($this->table_order);
		if($dataArr['bind_type']==1)
		{
			$Suffix = '_author'.$this->base->getSuffixTable($dataArr['author_buyer']);
		}
		else
		{
			$Suffix = '_author'.$this->base->getSuffixTable($dataArr['author_seller']);
		}		
		$TableName  = $this->pdo->createLogTable($table_name, $Suffix);  // 取新建的表名		
		return $this->pdo->replace($TableName,$dataArr);		
	}
	function insertOrderLogDate($dataArr)
	{
		$table_name = $this->base->getDbTable($this->table_order);
		$Suffix = '_date_'.date("Ym",$dataArr['deal_time']);				
		$TableName  = $this->pdo->createLogTable($table_name, $Suffix);  // 取新建的表名		
		return $this->pdo->replace($TableName,$dataArr);		
	}
	function insertOrderLog($dataArr)
	{
		$this->pdo->begin();
		
		$authorLog = $this->insertOrderLogAuthor($dataArr);
		$DateLog = $this->insertOrderLogDate($dataArr);
		$RemoveProcess = $this->deleteOrderFromProcess($dataArr);
		if($authorLog && $DateLog && $RemoveProcess)
		{
			echo "*";
			$this->pdo->commit();
			return true;
		}
		else
		{
			echo "-";
			$this->pdo->rollback();
			return false;
		}
		
	}
	function deleteOrderFromProcess($dataArr)
	{
		$table_name = $this->base->getDbTable($this->table_orderToPorcess);
		return $this->pdo->delete($table_name,'`order_id`=? and `bind_type`=?',array($dataArr['order_id'],$dataArr['bind_type']));
	}
	
	/**
	 * 获取登陆用户的绑定专属客服id
	 * @author 登陆用户名
	 * @return 绑定客服id
	 */ 
	function getBindAuthorId($author)
	{
		$table_name = $this->base->getDbTable($this->table_bindLog);
		$bindLogSuffix = '_author'.$this->base->getSuffixTable($author);
		$bindLogTableName  = $this->pdo->createLogTable($table_name, $bindLogSuffix);  // 取新建的表名
		
		$sql = "SELECT scid FROM $bindLogTableName WHERE author='$author' ORDER BY id DESC LIMIT 1";
		$scid =  $this->pdo->getOne($sql);
		if(!empty($scid))
		{
			return $scid;
		}
		else // 到user站点拿绑定客服信息
		{
			$url = "http://user.5173.com/ajax/GetUserBindScByLoginId.ashx?loginId=$author";
			$BindInfoJson = file_get_contents($url);
			$bindInfoArr = json_decode($BindInfoJson,true);
			if($bindInfoArr['BindKefu'] == '')
			{
				return false;
			}
			else
			{
				return $bindInfoArr['BindKefu'];
			}
		}
	}
}
?>
