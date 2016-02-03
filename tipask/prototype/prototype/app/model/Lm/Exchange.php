<?php
/**
 * 兑换处理
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Exchange.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Lm_Exchange extends Base_Widget
{

	/**
	 * 充值表
	 * @var string
	 */
	protected $table = 'lm_exchange';
	protected $table_exchange_queue = 'exchange_queue';
    protected $table_exchange_queue_error = 'exchange_queue_error';

	public function createExchangeQueueByOrder($OrderInfo)
	{
		$oOrder = new Lm_Order();
		$OrderInfo = $oOrder->getRow($OrderInfo['OrderId']);
		//订单存在
        if($OrderInfo['OrderId'])
		{
			//需要自动兑换
            if($OrderInfo['AppId']!=100)
			{
				//尚未执行扣款和添加兑换队列操作
                if($OrderInfo['OrderStatus']==1)
				{
					$time = time();
					//初始化兑换信息
                    $ExchangeInfo = array('OrderId'=>$OrderInfo['OrderId'],
					'AppId'=>$OrderInfo['AppId'],
					'PartnerId'=>$OrderInfo['PartnerId'],
                    'Coin'=>$OrderInfo['Coin'],
					'ServerId'=>$OrderInfo['ServerId'],
					'UserId'=>$OrderInfo['AcceptUserId'],
					'AppCoin'=>$OrderInfo['AppCoin'],
					'UserSourceId'=>$OrderInfo['UserSourceId'],
					'UserSourceDetail'=>$OrderInfo['UserSourceDetail'],
					'UserSourceProjectId'=>$OrderInfo['UserSourceProjectId'],
					'UserSourceActionId'=>$OrderInfo['UserSourceActionId'],
					'UserRegTime'=>$OrderInfo['UserRegTime'],
					'ExchangeStatus'=>0,
					'ReTryCount'=>0,
                    'ExchangeType'=>1,
                    'CreateExchangeTime'=>$time,
					'ExchangeId'=>date("YmdHis",$time).sprintf("%04d",rand(1,9999)),
					'ToSendTime'=>$time
					);                   
                    $oUser = new Lm_User();
					$UserInfo = $oUser->GetUserById($OrderInfo['AcceptUserId']);
                    //用户存在
                    if($UserInfo['UserId'])
                    {
                        //余额不足
                        if($UserInfo['UserCoin'] < $OrderInfo['Coin'])
                        {
                            return false;
                        }
                        //余额足够
                        else
                        {
                            $this->db->begin();
                            $queueTable = Base_Widget::getDbTable($this->table_exchange_queue);
    					    //添加兑换队列
                            $addQueue = $this->db->insert($queueTable,$ExchangeInfo);
                            //扣款
                            $coinUpdate = $oUser->updateUserCoin($UserInfo['UserId'],$OrderInfo['Coin']*(-1));
                            //更新订单
                            $updateOrderBind = array('OrderStatus'=>4,'ExchangeId'=>$ExchangeInfo['ExchangeId']);
                            $updateOrder = $oOrder->updateOrder($ExchangeInfo['OrderId'],$ExchangeInfo['UserId'],$updateOrderBind);
                            if($addQueue&&$coinUpdate&&$updateOrder)
        					{
        						//执行
                                $this->db->commit();
                                return 	$ExchangeInfo['ExchangeId'];
        					}
        					else
        					{
        						$this->db->rollBack();
                                return false; 	
        					}                       
                        }
                    }
                    else
                    {
                        return false;
                    }	
				}
				//已经执行过，无需重复执行
                elseif($OrderInfo['OrderStatus']>1)
				{
					return true;	
				}
                //尚未支付成功，不执行
				elseif($OrderInfo['OrderStatus']<1)
				{
					return false;	
				}
			}
			else
			{
				return true;	
			}	
		}
		else 
		{
		 	return false;
		}
	}
	public function createExchangeQueueByManager($UserName,$ServerId,$AppCoin,$ManagerName)
	{
		$oServer = new Config_Server();
		$ServerInfo = $oServer->getRow($ServerId);
		//服务器存在
		if($ServerInfo['ServerId'])
		{
			$oUser = new Lm_User();
			$UserInfo = $oUser->getUserbyName($UserName);
			//用户存在
			if($UserInfo['UserId'])
			{
				$time = time();
				//初始化兑换信息
                $ExchangeInfo = array('OrderId'=>0,
				'AppId'=>$ServerInfo['AppId'],
				'PartnerId'=>$ServerInfo['PartnerId'],
                'Coin'=>0,//不扣款
				'ServerId'=>$ServerInfo['ServerId'],
				'UserId'=>$UserInfo['UserId'],
				'AppCoin'=>$AppCoin,
				'UserSourceId'=>$UserInfo['UserSourceId'],
				'UserSourceDetail'=>$UserInfo['UserSourceDetail'],
				'UserSourceProjectId'=>$UserInfo['UserSourceProjectId'],
				'UserSourceActionId'=>$UserInfo['UserSourceActionId'],
				'UserRegTime'=>$UserInfo['UserRegTime'],
				'ExchangeStatus'=>0,
				'ReTryCount'=>0,
                'ExchangeType'=>2,
                'CreateExchangeTime'=>$time,
				'ExchangeId'=>date("YmdHis",$time).sprintf("%04d",rand(1,9999)),
				'Comment'=>json_encode(array('Manager'=>$ManagerName)),
				'ToSendTime'=>$time
				);                   
                $this->db->begin();
                $queueTable = Base_Widget::getDbTable($this->table_exchange_queue);
			    //添加兑换队列
                $addQueue = $this->db->insert($queueTable,$ExchangeInfo);
                //扣款
                if($addQueue)
				{
					//执行
                    $this->db->commit();
                    return 	$ExchangeInfo['ExchangeId'];
				}
				else
				{
					$this->db->rollBack();
                    return false; 	
				}                       
                    				
			}
			else
			{
				return false; 	
			}
		
		}
		else
		{
			return false;	
		}
	}
	public function createExchangeQueueByCode($UserId,$ServerId,$AppCoin,$ProductPackCode)
	{
		$oServer = new Config_Server();
		$ServerInfo = $oServer->getRow($ServerId);
		//服务器存在
		if($ServerInfo['ServerId'])
		{
			$oUser = new Lm_User();
			$UserInfo = $oUser->getUserbyId($UserId);
			//用户存在
			if($UserInfo['UserId'])
			{
				$time = time();
				//初始化兑换信息
                $ExchangeInfo = array('OrderId'=>0,
				'AppId'=>$ServerInfo['AppId'],
				'PartnerId'=>$ServerInfo['PartnerId'],
                'Coin'=>0,//不扣款
				'ServerId'=>$ServerInfo['ServerId'],
				'UserId'=>$UserInfo['UserId'],
				'AppCoin'=>$AppCoin,
				'UserSourceId'=>$UserInfo['UserSourceId'],
				'UserSourceDetail'=>$UserInfo['UserSourceDetail'],
				'UserSourceProjectId'=>$UserInfo['UserSourceProjectId'],
				'UserSourceActionId'=>$UserInfo['UserSourceActionId'],
				'UserRegTime'=>$UserInfo['UserRegTime'],
				'ExchangeStatus'=>0,
				'ReTryCount'=>0,
                'ExchangeType'=>4,
                'CreateExchangeTime'=>$time,
				'ExchangeId'=>date("YmdHis",$time).sprintf("%04d",rand(1,9999)),
				'Comment'=>json_encode(array('ProductPackCode'=>$ProductPackCode)),
				'ToSendTime'=>$time
				);                   
                $this->db->begin();
                $queueTable = Base_Widget::getDbTable($this->table_exchange_queue);
			    //添加兑换队列
                $addQueue = $this->db->insert($queueTable,$ExchangeInfo);
                //扣款
                if($addQueue)
				{
					//执行
                    $this->db->commit();
                    return 	$ExchangeInfo['ExchangeId'];
				}
				else
				{
					$this->db->rollBack();
                    return false; 	
				}                       
                    				
			}
			else
			{
				return false; 	
			}		
		}
		else
		{
			return false;	
		}
	}
    public function createExchangeQueueByUser($UserId,$ServerId,$Coin)
	{	   
		$oServer = new Config_Server();
        $oApp = new Config_App();
		$ServerInfo = $oServer->getRow($ServerId);
		//服务器存在
		if($ServerInfo['ServerId'])
		{
            $AppInfo = $oApp->getRow($ServerInfo['AppId']);
            if($AppInfo['AppId'])
            {
                $oUser = new Lm_User();
			    $UserInfo = $oUser->GetUserById($UserId);
                //用户存在
    			if($UserInfo['UserId'])
    			{
    				$time = time();
    				//初始化兑换信息
                    $ExchangeInfo = array('OrderId'=>0,
    				'AppId'=>$ServerInfo['AppId'],
    				'PartnerId'=>$ServerInfo['PartnerId'],
                    'Coin'=>$Coin,
    				'ServerId'=>$ServerInfo['ServerId'],
    				'UserId'=>$UserInfo['UserId'],
    				'AppCoin'=>($Coin*$AppInfo['exchange_rate']),
    				'UserSourceId'=>$UserInfo['UserSourceId'],
    				'UserSourceDetail'=>$UserInfo['UserSourceDetail'],
    				'UserSourceProjectId'=>$UserInfo['UserSourceProjectId'],
    				'UserSourceActionId'=>$UserInfo['UserSourceActionId'],
    				'UserRegTime'=>$UserInfo['UserRegTime'],
    				'ExchangeStatus'=>0,
    				'ReTryCount'=>0,
                    'ExchangeType'=>3,
                    'CreateExchangeTime'=>$time,
    				'ExchangeId'=>date("YmdHis",$time).sprintf("%04d",rand(1,9999)),			
 					'ToSendTime'=>$time
    				);                   
                    $this->db->begin();
                    $queueTable = Base_Widget::getDbTable($this->table_exchange_queue);
    			    //添加兑换队列
                    $addQueue = $this->db->insert($queueTable,$ExchangeInfo);
                    $coinUpdate = $oUser->updateUserCoin($UserInfo['UserId'],$Coin*(-1));
                    //扣款
                    if($addQueue&&$coinUpdate)
    				{
    					//执行
                        $this->db->commit();
                        return 	$ExchangeInfo['ExchangeId'];
    				}
    				else
    				{
    					$this->db->rollBack();
                        return false; 	
    				}                       
                        				
    			}
    			else
    			{
    				return false; 	
    			}
    		
    		}
    		else
    		{
    			return false;	
    		}
        }
        else
        {
            return false;
        }			
	}
	public function convertExchangeToSocket($ExchangeId)
	{
		$ExchangeInfo = $this->getQueuedExchange($ExchangeId);
		//订单存在
        echo "convert-------->\n";
        print_R($ExchangeInfo);
        if($ExchangeInfo['ExchangeId'])
		{
			if($ExchangeInfo['ToSendTime']<=time())
			{
				$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));
				$oSocketQueue = new Config_SocketQueue();
	            //订单状态为尚未通知服务器
				if($ExchangeInfo['ReTryCount']<3)
				{
		            if($ExchangeInfo['ExchangeStatus']==0)
					{		
						$uType=60215;
						$TypeInfo = $oSocketType[$uType];
						if($TypeInfo['Type'])
						{
							$DataArr = array('PackFormat'=>$TypeInfo['PackFormat'],
							'Length' => $TypeInfo['Length'],
		                    'Length2' => 0,
							'uType'=>$uType,
							'MsgLevel'=>0,
							'Line'=>0,
							'UserID'=>$ExchangeInfo['UserId'],
							'ZoneID'=>$ExchangeInfo['ServerId'],
							'iCash'=>$ExchangeInfo['AppCoin'],
							'ExchangeId'=>$ExchangeInfo['ExchangeId']);	
						}
						$DataArr = array('ServerId'=>$ExchangeInfo['ServerId'],'uType'=>$uType,'UserId'=>$DataArr['UserID'],'MessegeContent'=>serialize($DataArr),'QueueTime'=>time(),'SendTime'=>0);
						$this->db->begin();
						$addQueue = $oSocketQueue->insert($DataArr);
						$updateReTry = $this->updateExchangeQueue($ExchangeInfo['ExchangeId'],array('RetryCount'=>$ExchangeInfo['ReTryCount']+1,'ToSendTime'=>time()+60*pow(2,$ExchangeInfo['ReTryCount']+1)));
						echo "60215\n";
                        echo "addQueue:".$addQueue."\n";
                        echo "updateReTry:".$updateReTry."\n";
                        echo "=============>end\n";
                        if($addQueue&&$updateReTry)
						{
							$this->db->commit();
							return true;
						}
						else
						{
							$this->db->rollback();
							return false; 	
						}					
					}
		            //订单状态为已经通知服务器但未执行完毕
					elseif($ExchangeInfo['ExchangeStatus']==1)
					{
						$uType=60217;
						$TypeInfo = $oSocketType[$uType];
						if($TypeInfo['Type'])
						{
							$DataArr = array('PackFormat'=>$TypeInfo['PackFormat'],					
							'Length' => $TypeInfo['Length'],
		                    'Length2' => 0,
							'uType'=>$uType,
							'MsgLevel'=>0,
							'Line'=>0,
							'UserID'=>$ExchangeInfo['UserId'],
							'ZoneID'=>$ExchangeInfo['ServerId'],
							'SN'=>$ExchangeInfo['ExchangeSn'],
							'iCash'=>$ExchangeInfo['AppCoin'],
							'ExchangeId'=>$ExchangeInfo['ExchangeId']);	
						}	
						$DataArr = array('ServerId'=>$ExchangeInfo['ServerId'],'uType'=>$uType,'UserId'=>$DataArr['UserID'],'MessegeContent'=>serialize($DataArr),'QueueTime'=>time(),'SendTime'=>0);
						$this->db->begin();
						$addQueue = $oSocketQueue->insert($DataArr);
						$updateReTry = $this->updateExchangeQueue($ExchangeInfo['ExchangeId'],array('RetryCount'=>$ExchangeInfo['ReTryCount']+1,'ToSendTime'=>time()+60*pow(2,$ExchangeInfo['ReTryCount']+1)));
						echo "60217\n";
                        echo "addQueue:".$addQueue."\n";
                        echo "updateReTry:".$updateReTry."\n";
						if($addQueue&&$updateReTry)
						{
							$this->db->commit();
							return true;
						}
						else
						{
							$this->db->rollback();
							return false; 	
						}
					}
				}
				else
				{
				 	$this->ExchangeFail($ExchangeInfo['ExchangeId']);
				}
			}	
		}			
	}
	public function getQueuedExchange($ExchangeId, $fields = '*')
	{
		$table_to_prcess = Base_Widget::getDbTable($this->table_exchange_queue);
		return $this->db->selectRow($table_to_prcess , $fields, '`ExchangeId` = ?', $ExchangeId);
	}
	public function updateExchangeQueue($ExchangeId,$bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue);
		return $this->db->update($table_to_process, $bind ,'`ExchangeId` = ?',array($ExchangeId));			
	}
	public function updateExchangeSN($ExchangeId,$Sn)
	{
		$this->db->begin();
		$ExchangeInfo = $this->getQueuedExchange($ExchangeId);
		//尚未通知服务器
        if(($ExchangeInfo['ExchangeStatus']==0)&&($ExchangeInfo['ExchangeSn']==0))
		{
			$bind = array('ExchangeStatus'=>1,'NoticeTime'=>time(),'ExchangeSn'=>$Sn,'ReTryCount'=>0,'ToSendTime'=>time());
			$update = $this->updateExchangeQueue($ExchangeId,$bind);
			//$addQueue = $this->convertExchangeToSocket($ExchangeId);
			
			//echo "updateExchangeSN::::update:".$update."-add:".$addQueue."\n";
			if($update)
            //if($update&&$addQueue)
			{
				$this->db->commit();
				return true;	
			}
			else
			{
				$this->db->rollback();
				return false;	
			}
		}
		else
		{
			return true;	
		}	
	}
	public function endExchange($ExchangeId,$SN)
	{
		$this->db->begin();
		$ExchangeInfo = $this->getQueuedExchange($ExchangeId);
		//兑换订单存在
        if($ExchangeInfo['ExchangeId'])
		{
			//sn匹配
            if($SN==$ExchangeInfo['ExchangeSn'])
			{
				unset($ExchangeInfo['ToSendTime']);
				$ExchangeInfo['ExchangeTime'] = time();
				$ExchangeInfo['ExchangeStatus'] = 2;
				$Date = date("Ym",$ExchangeInfo['ExchangeTime']);
				//兑换日期表
                $table_date = $this->createUserExchangeTableDate($Date);
				//兑换用户表
                $table_user = $this->createUserExchangeTableUser($ExchangeInfo['UserId']);
			    //添加日期表
                $Date = $this->db->insert($table_date,$ExchangeInfo);
				//添加用户表
                $User = $this->db->insert($table_user,$ExchangeInfo);
				//删除兑换队列
                $DelQueue = $this->deleteExchangeQueue($ExchangeId);
				//初始化订单修改
                $updateOrder = false;
				//订单存在
                if($ExchangeInfo['OrderId'])
				{
					$OrderUpdateBind = array('ExchangeID'=>$ExchangeInfo['ExchangeId'],'OrderStatus'=>2);
					$oOrder = new Lm_Order();
					//更新订单记录
                    $updateOrder = $oOrder->updateOrder($ExchangeInfo['OrderId'],$ExchangeInfo['UserId'],$OrderUpdateBind);
				}
				else
				{
				 	 //无需更新，直接通过
                     $updateOrder = true;
				}
				if($Date&&$User&&$updateOrder&&$DelQueue)
				{
					$this->db->commit();
					//由码兑换带入
					if($ExchangeInfo['ExchangeType']==4)
					{
						$oProduct = new Config_Product_Product();
						$oProductPack = new Config_Product_Pack();
						$Comment = json_decode($ExchangeInfo['Comment'],true);
						//移除道具发送队列
						$remove = $oProduct->removeSentLog($Comment['ProductPackCode'],$ExchangeInfo['AppId'],0);
						//获取礼包信息
						//$PackCode = $oProductPack->getProductPackCode($Comment['ProductPackCode']);
						//解开备注字段
						//$C = json_decode($PackCode['Comment']);
						//添加兑换ID
						//$C['ExchangeId'] = $ExchangeInfo['ExchangeId'];
						//更新兑换ID
						//$oProductPack->updatePackCode($Comment['ProductPackCode'],array('Comment'=>json_encode($C)));								
					}

					return true;	
				}
				else
				{
					$this->db->rollback();
					return false;	
				}
			}
			else
			{
				return false; 	
			}	
		}
		else
		{
		 	return false;
		}		
	}
	public function createUserExchangeTableDate($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);

		$table_to_process = Base_Widget::getDbTable($this->table_date)."_date_".$Date;
		
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 return false;	
			}		 	
		}
	}
	public function createUserExchangeTableUser($UserId)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);

		$position = Base_Common::getUserDataPositionById($UserId);
		
		$table_to_process = Base_Widget::getDbTable($this->table_user)."_user_".$position['db_fix'];
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 return false;	
			}		 	
		}
	}
	public function deleteExchangeQueue($ExchangeId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue);
		return $this->db->delete($table_to_process,'`ExchangeId` = ?',array($ExchangeId));
	}    
    public function addErrorExchange($ExchangeInfo)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_exchange_queue)."_error";
        return $this->db->insert($table_to_process,$ExchangeInfo);
    }    
    public function ExchangeFail($ExchangeId)
    {
        $ExchangeInfo = $this->getQueuedExchange($ExchangeId);
        if($ExchangeInfo['ExchangeId'])
        {
            $this->db->begin();
            //移除到兑换失败队列
			unset($ExchangeInfo['ToSendTime']);
            $addErrorExchangeQueue = $this->addErrorExchange($ExchangeInfo);
            $delExchangeQueue = $this->deleteExchangeQueue($ExchangeInfo['ExchangeId']);
            
            $updateOrderStatus = false;
            //更新订单状态
            if($ExchangeInfo['OrderId'])
            {                
                $oOrder = new Lm_Order();                
            	$updateOrderStatus = $oOrder->updateOrder($ExchangeInfo['OrderId'],$ExchangeInfo['UserId'],array('OrderStatus'=>'3'));
        	}
        	else
        	{
        		$updateOrderStatus = true; 	
        	}
            //退款
            $oUser = new Lm_User();
            $coinUpdate = $oUser->updateUserCoin($ExchangeInfo['UserId'],$ExchangeInfo['Coin']);
            
            if($addErrorExchangeQueue && $delExchangeQueue && $updateOrderStatus && $coinUpdate)
            {
                $this->db->commit();
                return true;
            }
            else
            {
                $this->db->rollback();
                return false;
            }
        }
        else
        {
            return false;
        }
    }
 	public function getExchangeQueueDetail($UserId,$ExchangeStatus,$ExchangeType,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		$ExchangeQueueCount = $this->getExchangeQueueDetailCount($UserId,$ExchangeStatus,$ExchangeType,$ServerId,$oWherePartnerPermission);
		if($ExchangeQueueCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
			$whereStatus = $ExchangeStatus!=-1?" ExchangeStatus = ".$ExchangeStatus." ":"";
			$whereType = $ExchangeType?" ExchangeType = ".$ExchangeType." ":"";
	
			$whereCondition = array($whereUser,$whereStatus,$whereType,$whereServer,$oWherePartnerPermission);
			
			$order = " order by CreateExchangeTime ";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		    
			$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue);    		
		    		    		    
		    $StatArr = array('ExchangeQueueDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$ExchangeQueueDetailArr = $this->db->getAll($sql,false);
			if(isset($ExchangeQueueDetailArr))
		    {
		    	foreach ($ExchangeQueueDetailArr as $key => $value) 
				{
					$StatArr['ExchangeQueueDetail'][$value['ExchangeId']] = $value;
				}
		    }
  	}
  	
	 	$StatArr['ExchangeQueueCount'] = $ExchangeQueueCount; 
		return $StatArr;
	}
 	public function getExchangeQueueDetailCount($UserId,$ExchangeStatus,$ExchangeType,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('ExchangeQueueCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		$whereStatus = $ExchangeStatus!=-1?" ExchangeStatus = ".$ExchangeStatus." ":"";
		$whereType = $ExchangeType?" ExchangeType = ".$ExchangeType." ":"";

		$whereCondition = array($whereUser,$whereStartDate,$whereEndDate,$whereStatus,$whereType,$whereServer,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
		$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue);    		

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$ExchangeQueueCount = $this->db->getOne($sql,false);
		if($ExchangeQueueCount)
    	{
			return $ExchangeQueueCount;    
		}
		else
		{
			return 0; 	
		}
	}
    public function getExchangeQueueToProcess($limit)
    {
        $select_fields = array('AppId','PartnerId','ServerId','UserId',
        'MinExchangeId'=>'min(ExchangeId)');
        //生成查询列
		$fields = Base_common::getSqlFields($select_fields);        
        
        $table_to_process = Base_Widget::getDbTable($this->table_exchange_queue);
        $group_fields = array('AppId','PartnerId','ServerId','UserId');
        $groups = Base_common::getGroupBy($group_fields);
        
        $sql = "SELECT $fields FROM $table_to_process $groups order by MinExchangeId limit 0,$limit";
        return $this->db->getAll($sql);
    }
 	public function getExchangeDetail($StartTime,$EndTime,$UserId,$ExchangeType,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		
		$ExchangeCount = $this->getExchangeDetailCount($StartTime,$EndTime,$UserId,$ExchangeType,$ServerId,$oWherePartnerPermission);
		if($ExchangeCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" ExchangeTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" ExchangeTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
			$whereType = $ExchangeType?" ExchangeType = ".$ExchangeType." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereType,$whereServer,$oWherePartnerPermission);
			
			$order = " order by ExchangeTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		    if($UserId)
		    {
					$position = Base_Common::getUserDataPositionById($UserId);			
					$table_to_process = Base_Widget::getDbTable($this->table)."_user_".$position['db_fix'];    		
		    }
		    else
		    {
					$Date = date("Ym",strtotime($StartTime));			
					$table_to_process = Base_Widget::getDbTable($this->table)."_date_".$Date;     	
		    }
		    $StatArr = array('ExchangeDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$ExchangeDetailArr = $this->db->getAll($sql,false);
			if(isset($ExchangeDetailArr))
		    {
				foreach ($ExchangeDetailArr as $key => $value) 
				{
					$StatArr['ExchangeDetail'][$value['ExchangeId']] = $value;
				}
		    }
	  	}
  	
	 	$StatArr['ExchangeCount'] = $ExchangeCount; 
		return $StatArr;
	}
 	public function getExchangeDetailCount($StartTime,$EndTime,$UserId,$ExchangeType,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('ExchangeCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" ExchangeTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" ExchangeTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		$whereType = $ExchangeType?" ExchangeType = ".$ExchangeType." ":"";

		$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereType,$whereServer,$oWherePartnerPermission);
				
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	    if($UserId)
	    {
				$position = Base_Common::getUserDataPositionById($UserId);			
				$table_to_process = Base_Widget::getDbTable($this->table)."_user_".$position['db_fix'];    		
	    }
	    else
	    {
				$Date = date("Ym",strtotime($StartTime));			
				$table_to_process = Base_Widget::getDbTable($this->table)."_date_".$Date;     	
	    }
    	$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$ExchangeCount = $this->db->getOne($sql,false);
		if($ExchangeCount)
    	{
			return $ExchangeCount;    
		}
		else
		{
			return 0; 	
		}
	}
	/*
	*selena 获取兑换信息
	**/
	public function getExchangeInfo($ExchangeId)
	{
		$ExchangeInfo = $this->getQueuedExchange($ExchangeId);
		if($ExchangeInfo['ExchangeId'])
		{
			return $ExchangeInfo;			
		}
		else
		{
		 	$ExchangeInfo = $this->getExchange($ExchangeId);
		 	if($ExchangeInfo['ExchangeId'])
		 	{
		 		return $ExchangeInfo;	
		 	}
		 	else 
		 	{
		 	 	$ExchangeInfo = $this->getFailedExchange($ExchangeId);
			 	if($ExchangeInfo['ExchangeId'])
			 	{
			 		return $ExchangeInfo;	
			 	}
			 	else
			 	{
			 		return false;			 	
			 	}
		 	}
		}
	}
	public function getExchange($ExchangeId, $fields = '*')
	{
		$Date = substr($ExchangeId,0,6);
		
		$table_to_prcess = Base_Widget::getDbTable($this->table)."_date_".$Date;
		return $this->db->selectRow($table_to_prcess , $fields, '`ExchangeId` = ?', $ExchangeId);
	}
	public function getFailedExchange($ExchangeId, $fields = '*')
	{
		$Date = substr($ExchangeId,0,6);
		
		$table_to_prcess = Base_Widget::getDbTable($this->table_exchange_queue_error);
		return $this->db->selectRow($table_to_prcess , $fields, '`ExchangeId` = ?', $ExchangeId);
	}
 	public function getUserExchangeList($UserId,$AppId,$PartnerId,$ServerId,$StartDate,$EndDate,$PageSize,$start,$ExchangeStatus)
	{
		$ExchangeCount = $this->getUserExchangeCount($UserId,$AppId,$PartnerId,$ServerId,$StartDate,$EndDate,$ExchangeStatus);
		if($ExchangeCount['ExchangeCount'])
		{
			//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartDate = $StartDate?" CreateExchangeTime >= ".strtotime($StartDate)." ":"";
			$whereEndDate = $EndDate?" CreateExchangeTime <= ".(strtotime($EndDate)+86400-1)." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
			$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";
			$whereApp = $AppId?" AppId = ".$AppId." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$wherePartner,$whereApp,$whereUser);

			if($ExchangeStatus==0)
			{
				//进行中
				$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue);
			}
			elseif($ExchangeStatus==1)
			{
				//已成功
		    	$position = Base_Common::getUserDataPositionById($UserId);	
				$table_to_process = Base_Widget::getDbTable($this->table_user)."_user_".$position['db_fix'];
			}
			else
			{
				//已失败
				$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue_error);
			}
			
			$order = " order by CreateExchangeTime desc";
			$limit = $PageSize?" limit $start,$PageSize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);

	    
			$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;

			$ExchangeDetailArr = $this->db->getAll($sql,false);
			if(isset($ExchangeDetailArr))
			{
        		foreach ($ExchangeDetailArr as $key => $value) 
				{
					$StatArr['ExchangeDetail'][$value['ExchangeId']] = $value;
				}
			}
	    
		}
	 	$StatArr['ExchangeCount'] = $ExchangeCount['ExchangeCount']; 
	 	return $StatArr;   
	}	
 	public function getUserExchangeCount($UserId,$AppId,$PartnerId,$ServerId,$StartDate,$EndDate,$ExchangeStatus)
	{
		//查询列
		$select_fields = array('ExchangeCount'=>'count(*)');

		//初始化查询条件
		$whereStartDate = $StartDate?" CreateExchangeTime >= ".strtotime($StartDate)." ":"";
		$whereEndDate = $EndDate?" CreateExchangeTime <= ".(strtotime($EndDate)+86400-1)." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";
		$whereApp = $AppId?" AppId = ".$AppId." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$wherePartner,$whereApp,$whereUser);
		
		if($ExchangeStatus==0)
		{
			//进行中
			$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue);
		}
		elseif($ExchangeStatus==1)
		{
			//已成功
	    	$position = Base_Common::getUserDataPositionById($UserId);	
			$table_to_process = Base_Widget::getDbTable($this->table_user)."_user_".$position['db_fix'];
		}
		else
		{
			//已失败
			$table_to_process = Base_Widget::getDbTable($this->table_exchange_queue_error);
		}
					
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

    	$StatArr = array('ExchangeCount'=>0);    
		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
        
		$ExchangeCount = $this->db->getRow($sql,false);
		if(isset($ExchangeCount))
    	{
			$StatArr['ExchangeCount'] += $ExchangeCount['ExchangeCount'];
    	} 
   		return $StatArr;
	}
 	public function getExchangeDay($StartDate,$EndDate,$ExchangeType,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'ExchangeUser'=>'count(distinct(UserId))',
		'ExchangeCount'=>'count(*)',
		'TotalAppCoin'=>'sum(AppCoin)',
		'Date'=>"from_unixtime(ExchangeTime,'%Y-%m-%d')");
		//分类统计列
		$group_fields = array('Date','AppId','PartnerId');

		//初始化查询条件
		$whereStartDate = $StartDate?" ExchangeTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" ExchangeTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereType = $ExchangeType?" ExchangeType = ".$ExchangeType." ":"";
		$whereCondition = array($whereStartTime,$whereEndTime,$oWherePartnerPermission,$whereType);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('ExchangeUser'=>0,'ExchangeCount'=>0,'TotalAppCoin'=>0);
		do
		{
			$StatArr['ExchangeDate'][$date] = array('ExchangeUser'=>0,'ExchangeCount'=>0,'TotalAppCoin'=>0);
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
	    $DateStart = date("Ym",strtotime($StartDate));
	    $DateEnd = date("Ym",strtotime($EndDate));
	    $DateList = array();
	    $Date = $StartDate;
	    do
	    {
	        $D = date("Ym",strtotime($Date));
	        $DateList[] = $D;
	        $Date = date("Y-m-d",strtotime("$Date +1 month"));
	    }
	    while($D!=$DateEnd);
	    $oPartnerApp = new Config_Partner_App();
	    $oArea = new Config_Area();
	    foreach($DateList as $key => $value)
	    {
			$table_name = Base_Widget::getDbTable($this->table)."_date_".$value;     	

			$sql = "SELECT  $fields FROM $table_name as log where 1 ".$where.$groups;
			$ExchangeDateArr = $this->db->getAll($sql);
			if(is_array($ExchangeDateArr))
			{
				foreach ($ExchangeDateArr as $key => $Stat) 
				{
					if(isset($StatArr['ExchangeDate'][$Stat['Date']]))
					{
						$StatArr['ExchangeDate'][$Stat['Date']]['ExchangeCount'] += $Stat['ExchangeCount'];
						$StatArr['ExchangeDate'][$Stat['Date']]['ExchangeUser'] += $Stat['ExchangeUser'];
						$StatArr['ExchangeDate'][$Stat['Date']]['TotalAppCoin'] += $Stat['TotalAppCoin'];
					}
					else
					{
						$StatArr['ExchangeDate'][$Stat['Date']] = array('ExchangeUser'=>0,'ExchangeCount'=>0,'TotalAppCoin'=>0);
						$StatArr['ExchangeDate'][$Stat['Date']]['ExchangeCount'] += $Stat['ExchangeCount'];
						$StatArr['ExchangeDate'][$Stat['Date']]['ExchangeUser'] += $Stat['ExchangeUser'];
						$StatArr['ExchangeDate'][$Stat['Date']]['TotalAppCoin'] += $Stat['TotalAppCoin'];
					}							
				}
			}
    	}
		return $StatArr;
	}
	public function testExchange()
	{
						$ExchangeInfo['Comment'] = json_encode(array('ProductPackCode'=>'jChrsrjMs'));
						$ExchangeInfo['AppId'] = 101;
						$ExchangeInfo['ExchangeId'] = '1234567896134567489';
						$oProduct = new Config_Product_Product();
						$oProductPack = new Config_Product_Pack();
						$Comment = json_decode($ExchangeInfo['Comment'],true);
						//移除道具发送队列
						$remove = $oProduct->removeSentLog($Comment['ProductPackCode'],$ExchangeInfo['AppId'],0);
						//获取礼包信息
						$PackCode = $oProductPack->getProductPackCode($Comment['ProductPackCode']);
						//解开备注字段
						$C = json_decode($PackCode['Comment']);
						//添加兑换ID
						$C['ExchangeId'] = $ExchangeInfo['ExchangeId'];
						//更新兑换ID
						$oProductPack->updatePackCode($Comment['ProductPackCode'],array('Comment'=>json_encode($C)));		
	}
}
