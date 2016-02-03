<?php
/**
 * Product配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Product.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Product_Product extends Base_Widget
{
	/**
	 * Product表名
	 * @var string
	 */
	protected $table = 'game_product';
	protected $table_send_queue = 'product_send_queue';
	protected $table_send_log = 'product_send_log';

	/**
	 * 获取单条记录
	 * @param integer $ProductId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($ProductId,$AppId,$field = '*')
	{
		$ProductId = intval($ProductId);
		$AppId = intval($AppId);
		return $this->db->selectRow($this->getDbTable(), $field, '`ProductId` = ? and `AppId` = ?', array($ProductId,$AppId));
	}
	
	/**
	 * 获取单个字段
	 * @param integer $ProductId
	 * @param string $field
	 * @return string
	 */
	public function getOne($ProductId,$AppId,$field)
	{
		$ProductId = intval($ProductId);
		$AppId = intval($AppId);
		return $this->db->selectOne($this->getDbTable(), $field, '`ProductId` = ? and `AppId` = ?', array($ProductId,$AppId));
	}
	/**
	 * 获取单个字段
	 * @param integer $ProductId
	 * @param string $field
	 * @return string
	 */
	public function getOneByName($ProductName,$AppId,$field)
	{
		$ProductName = trim($ProductName);
		$AppId = intval($AppId);
		return $this->db->selectOne($this->getDbTable(), $field, '`name` = ? and `AppId` = ?', array($ProductName,$AppId));
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
	 * 删除
	 * @param integer $ProductId
	 * @return boolean
	 */
	public function delete($ProductId,$AppId)
	{
		$ProductId = intval($ProductId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`ProductId` = ? and `AppId` = ?', array($ProductId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $ProductId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($ProductId,$AppId, array $bind)
	{
		$ProductId = intval($ProductId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`ProductId` = ? and `AppId` = ?', array($ProductId,$AppId));
	}

	public function getAll($AppId,$ProductTypeId,$fields = "*")
	{
		//初始化查询条件
		$whereApp = $AppId?" AppId = $AppId":"";
		$whereProductType = $ProductTypeId?" ProductTypeId = $ProductTypeId":"";

		$whereCondition = array($whereApp,$whereProductType);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		$sql = "SELECT $fields FROM " . $this->getDbTable($this->table) . " where 1 ".$where." ORDER BY AppId,ProductTypeId,ProductId ASC";
		$return = $this->db->getAll($sql);
		
		$AllProduct = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllProduct[$value['AppId']][$value['ProductId']] = $value;	
			}	
		}
		return $AllProduct;
	}
	public function insertIntoProductSendList($SendId,$SendType,$ProductId,$ProductType,$ProductCount,$UserId,$ServerId,$ToSendTime)
	{
		$bind = array('SendId'=>$SendId,'SendType'=>$SendType,'ProductId'=>$ProductId,'ProductType'=>$ProductType,'ProductCount'=>$ProductCount,'UserId'=>$UserId,'ServerId'=>$ServerId,'ToSendTime'=>$ToSendTime);
		return $this->db->insert($this->getDbTable($this->table_send_queue), $bind);
	}
    public function getProductQueueToProcess($limit)
    {
        $select_fields = array('*');
        //生成查询列
		$fields = Base_common::getSqlFields($select_fields);                
        $table_to_process = Base_Widget::getDbTable($this->table_send_queue);        
        $sql = "SELECT $fields FROM $table_to_process where ToSendTime <= ".time()." and SendStatus = 0 order by UserId,SendId limit 0,$limit";
        return $this->db->getAll($sql);
    }
	public function convertProductToSocket($ProductInfo)
	{
        //订单状态为尚未通知服务器
		if($ProductInfo['SendStatus']==0)
		{			
			$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));
			$oSocketQueue = new Config_SocketQueue();	
			//发放英雄
			if($ProductInfo['ProductType']=="hero")
			{					
				$uType=60227;
				$TypeInfo = $oSocketType[$uType];
				if($TypeInfo['Type'])
				{
					$DataArr = array('PackFormat'=>$TypeInfo['PackFormat'],
					'Length' => $TypeInfo['Length'],
                    'Length2' => 0,
					'uType'=>$uType,
					'MsgLevel'=>0,
					'Line'=>0,
					'UserID'=>$ProductInfo['UserId'],
					'HeroID'=>$ProductInfo['ProductId'],
					'Serial'=>$ProductInfo['SendId']);	
				}
				$DataArr = array('ServerId'=>$ProductInfo['ServerId'],'uType'=>$uType,'UserId'=>$DataArr['UserID'],'MessegeContent'=>serialize($DataArr),'QueueTime'=>time(),'SendTime'=>0);
                $this->db->begin();
				$addQueue = $oSocketQueue->insert($DataArr);
				$updateStatus = $this->updateProductQueue($ProductInfo['SendId'],$ProductInfo['ProductId'],array('SendStatus'=>1));
				if($addQueue&&updateStatus)
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
			//发放时装
			if($ProductInfo['ProductType']=="skin")
			{					
				$uType=60229;
				$TypeInfo = $oSocketType[$uType];
				if($TypeInfo['Type'])
				{
					$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
					$ServerInfo = $ServerList[$ProductInfo['ServerId']];
					$oSkin = new Config_Skin();
					$SkinInfo = $oSkin->getRow($ProductInfo['ProductId'],$ServerInfo['AppId']);
					$DataArr = array('PackFormat'=>$TypeInfo['PackFormat'],
					'Length' => $TypeInfo['Length'],
                    'Length2' => 0,
					'uType'=>$uType,
					'MsgLevel'=>0,
					'Line'=>0,
					'UserID'=>$ProductInfo['UserId'],
					'HeroID'=>$SkinInfo['HeroId'],
					'HeroEquip'=>$ProductInfo['ProductId'],
					'Serial'=>$ProductInfo['SendId']);	
				}
				$DataArr = array('ServerId'=>$ProductInfo['ServerId'],'uType'=>$uType,'UserId'=>$DataArr['UserID'],'MessegeContent'=>serialize($DataArr),'QueueTime'=>time(),'SendTime'=>0);
                $this->db->begin();
				$addQueue = $oSocketQueue->insert($DataArr);
				$updateStatus = $this->updateProductQueue($ProductInfo['SendId'],$ProductInfo['ProductId'],array('SendStatus'=>1));
				if($addQueue&&updateStatus)
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
			//发放金钱
			if($ProductInfo['ProductType']=="money")
			{					
				$uType=60223;
				$TypeInfo = $oSocketType[$uType];
				if($TypeInfo['Type'])
				{
					$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
					$ServerInfo = $ServerList[$ProductInfo['ServerId']];
					$oSkin = new Config_Skin();
					$SkinInfo = $oSkin->getRow($ProductInfo['ProductId'],$ServerInfo['AppId']);
					$DataArr = array('PackFormat'=>$TypeInfo['PackFormat'],
					'Length' => $TypeInfo['Length'],
                    'Length2' => 0,
					'uType'=>$uType,
					'MsgLevel'=>0,
					'Line'=>0,
					'UserID'=>$ProductInfo['UserId'],
					'MoneyChanged'=>$ProductInfo['ProductCount'],
					'MoneyType'=>$ProductInfo['ProductId'],
					'Serial'=>$ProductInfo['SendId']);	
				}
				$DataArr = array('ServerId'=>$ProductInfo['ServerId'],'uType'=>$uType,'UserId'=>$DataArr['UserID'],'MessegeContent'=>serialize($DataArr),'QueueTime'=>time(),'SendTime'=>0);
                $this->db->begin();
				$addQueue = $oSocketQueue->insert($DataArr);
				$updateStatus = $this->updateProductQueue($ProductInfo['SendId'],$ProductInfo['ProductId'],array('SendStatus'=>1));
				if($addQueue&&updateStatus)
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
			//发放游戏币
			if($ProductInfo['ProductType']=="appcoin")
			{					
				$oProductPack = new Config_Product_Pack();
				$CodeInfo = $oProductPack->getUserProductPackCode($ProductInfo['SendId'],$ProductInfo['UserId']);
				$Comment = json_decode($CodeInfo['Comment'],true);
				if(!isset($Comment['ExchangeId']))
				{
					$oExchange = new Lm_Exchange();
					$createExchange = $oExchange->createExchangeQueueByCode($ProductInfo['UserId'],$ProductInfo['ServerId'],$ProductInfo['ProductCount'],$ProductInfo['SendId']);
					$this->db->begin();
					if($createExchange)
					{
						$updateStatus = $this->updateProductQueue($ProductInfo['SendId'],$ProductInfo['ProductId'],array('SendStatus'=>1));
						$Comment['ExchangeId'] = $createExchange;
						$updateCode = $oProductPack->updatePackCode($ProductInfo['SendId'],array('Comment'=>json_encode($Comment)));
						if($updateStatus&&$updateCode)
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
						$this->db->rollback();
						return false; 	
					}					
				}
				else
				{
					return false; 	
				}					
			}								
		}
	}
	public function updateProductQueue($SendId,$ProductId,$bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_send_queue);
		return $this->db->update($table_to_process, $bind ,'`SendId` = ? and `ProductId` = ?',array($SendId,$ProductId));			
	}
	public function getProductQueue($SendId,$ProductId,$field = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_send_queue);
		return $this->db->selectRow($table_to_process,$field,'`SendId` = ? and `ProductId` = ?',array($SendId,$ProductId));			
	}
	public function deleteProductQueue($SendId,$ProductId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_send_queue);
		return $this->db->delete($table_to_process,'`SendId` = ? and `ProductId` = ?',array($SendId,$ProductId));			
	}
	public function createUserProductSendLogTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_send_log);
		$table_to_process = Base_Widget::getDbTable($this->table_send_log)."_".date('Ym',strtotime($Date));
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
			$sql = str_replace('`' . $this->table_send_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
	public function removeSentLog($Serial,$ProductId,$Result)
	{		
		$ProductQueue = $this->getProductQueue(trim($Serial),$ProductId);
		if($ProductQueue['SendId'])
		{
			$ProductQueue['SentTime'] = time();
			unset($ProductQueue['ToSendTime']);
			if($Result==0)
			{
				$ProductQueue['SendStatus']=1;
			}
			else
			{
				$ProductQueue['SendStatus']=2;
			}
			$table_date = $this->createUserProductSendLogTable(date("Ym",$ProductQueue['SentTime']));

			$oCharacter = new Lm_Character();
            
            $User = $oCharacter->insertCharacterProductSendLog($ProductQueue['UserId'],$ProductQueue);
			if($User)
            {    
                $this->db->begin();
                $Date = $this->db->replace($table_date,$ProductQueue);
    			$remove = $this->deleteProductQueue(trim($Serial),$ProductId);
    			echo $Date."-".$remove."\n";
                if($Date&&$remove)
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
		else
		{
			return false; 	
		}
	}
 	public function getProductSendQueueDetail($UserId,$ProductSendType,$ProductType,$ServerId,$start,$pagesize)
	{
		$ProductSendQueueCount = $this->getProductSendQueueDetailCount($UserId,$ProductSendType,$ProductType,$ServerId);
		if($ProductSendQueueCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
			$whereSendType = $ProductSendType?" SendType = '".$ProductSendType."' ":"";
			$whereType = $ProductType?" ProductType = '".$ProductType."' ":"";
	
			$whereCondition = array($whereUser,$whereSendType,$whereType,$whereServer);
			
			$order = " order by ToSendTime ";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		    
			$table_to_process = Base_Widget::getDbTable($this->table_send_queue);    		
		    		    		    
		    $StatArr = array('ProductSendQueueDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;

			$ProductSendQueueDetailArr = $this->db->getAll($sql,false);
			if(isset($ProductSendQueueDetailArr))
		    {
		    	foreach ($ProductSendQueueDetailArr as $key => $value) 
				{
					$StatArr['ProductSendQueueDetail'][] = $value;
				}
		    }
  	}
  	
	 	$StatArr['ProductSendQueueCount'] = $ProductSendQueueCount; 
		return $StatArr;
	}
 	public function getProductSendQueueDetailCount($UserId,$ProductSendType,$ProductType,$ServerId)
	{
		//查询列
		$select_fields = array('ProductSendQueueCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		$whereSendType = $ProductSendType?" SendType = '".$ProductSendType."' ":"";
		$whereType = $ProductType?" ProductType = '".$ProductType."' ":"";

		$whereCondition = array($whereUser,$whereSendType,$whereType,$whereServer);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
		$table_to_process = Base_Widget::getDbTable($this->table_send_queue);    		

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$ProductSendQueueCount = $this->db->getOne($sql,false);
		if($ProductSendQueueCount)
    	{
			return $ProductSendQueueCount;    
		}
		else
		{
			return 0; 	
		}
	}
	public function resetProductQueue()
	{
		$table_to_update = Base_Widget::getDbTable($this->table_send_queue);
		$array = array(1,time());
		return $this->db->update($table_to_update, array("SendStatus"=>0), '`SendStatus` = ? and ( ? - `ToSendTime`) >= 60*30',$array);
	}

}
