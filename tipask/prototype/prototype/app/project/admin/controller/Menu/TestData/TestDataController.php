<?php
/**
 * 测试数据生成
 * @author Chen<cxd032404@hotmail.com>
 * $Id: TestDataController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class TestData_TestDataController extends AbstractController
{
	/**
	 * App对象
	 * @var object
	 */
	protected $oApp;
	protected $oClass;
	protected $oPartner;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		set_time_limit(0);
		$this->oTestUser = new GameDataSync_User();
	}

	public function activeAction()
	{
		$sex = array(-1,1);
		$num = $this->ququest->num?intval(abs($this->request->num)):50000;	
		$r = 0;
		for($i=1;$i<=$num;$i++)
		{
			$n1 = sprintf("%03d",mt_rand(0,999));
			$n2 = sprintf("%03d",mt_rand(0,999));
			$n3 = sprintf("%03d",mt_rand(0,999));
			$n4 = sprintf("%03d",mt_rand(0,999));
			$n5 = sprintf("%03d",mt_rand(0,999));
			$n6 = sprintf("%03d",mt_rand(0,999));
			$n7 = sprintf("%03d",mt_rand(0,999));
			$n8 = sprintf("%03d",mt_rand(0,999));
			$AppId= rand(1,4);
			$PartnerId=1;
			$user = array(
			'UserAccount'=> $n1,//.$n3.$n4,
			'UserId'=>0,
			'AppId'=>$AppId,
			'PartnerId'=>$PartnerId,
			'ServerId'=>$AppId.sprintf("%03d",$PartnerId).sprintf("%03d",rand(1,4)),
			'CreateTime'=>time()-rand(86400*40,86400*60),
			'CharacterName'=>$n5.$n6.$n7.$n8,
			'CharacterJob'=>rand(1,3),
			'CharacterBirthMap'=>rand(1,3),
			'CharacterSex'=>$sex[intval(rand(0,1))],
			);
			$return = $this->oTestUser->InsertActiveUser($user);
			$r += $return;

		}
		echo $r;
	}
	public function gameLoginAction()
	{
		$num = $this->ququest->num?intval(abs($this->request->num)):50000;	
		$r = 0;
		for($i=1;$i<=$num;$i++)
		{
			$n1 = sprintf("%03d",mt_rand(0,999));
			$n2 = sprintf("%03d",mt_rand(0,999));
			$n3 = sprintf("%03d",mt_rand(0,999));
			$n4 = sprintf("%03d",mt_rand(0,999));
			
			$n5 = sprintf("%03d",mt_rand(0,999));
			$n6 = sprintf("%03d",mt_rand(0,999));
			$n7 = sprintf("%03d",mt_rand(0,999));
			$n8 = sprintf("%03d",mt_rand(0,999));
			$AppId= rand(1,4);
			$PartnerId=1;
			$login = array(
			'UserAccount'=>$n1,
			'UserId'=>0,
			'AppId'=>$AppId,
			'PartnerId'=>$PartnerId,
			'ServerId'=>$AppId.sprintf("%03d",$PartnerId).sprintf("%03d",rand(1,4)),
			'LoginTime'=>time()-rand(8400*10,86400*20),
			'LoginIp'=> mt_rand(0,127).".".mt_rand(0,127).".".mt_rand(0,127).".".mt_rand(0,127),
			'LoginMap'=>rand(1,3),
			);
			$return = $this->oTestUser->InsertGameLogin($login);
			$r += $return;

		}
		echo $r;
	}
	public function levelUpAction()
	{
		$num = $this->ququest->num?intval(abs($this->request->num)):100000;	
		$r = 0;
		for($i=1;$i<=$num;$i++)
		{
			$n1 = sprintf("%03d",mt_rand(0,999));
			$n2 = sprintf("%03d",mt_rand(0,999));
			$n3 = sprintf("%03d",mt_rand(0,999));
			$n4 = sprintf("%03d",mt_rand(0,999));
			
			$n5 = sprintf("%03d",mt_rand(0,999));
			$n6 = sprintf("%03d",mt_rand(0,999));
			$n7 = sprintf("%03d",mt_rand(0,99));
			$n8 = sprintf("%03d",mt_rand(0,99));
			$AppId= rand(1,4);
			$PartnerId=1;
			$StartLevel=0;
			$levelUp = $n7;
			$LevelUPTime = time()-rand(86400*5,86400*20);
			for($i=0;$i<=$levelUp;$i++)
			{
				$LevelUPTime = $LevelUPTime+rand(10,10000);
				$levelup = array(
				'UserAccount'=>$n1.$n2,
				'UserId'=>0,
				'CharacterName'=>$n2.$n3.$n4,
				'AppId'=>$AppId,
				'PartnerId'=>$PartnerId,
				'ServerId'=>$AppId.sprintf("%03d",$PartnerId).sprintf("%03d",rand(1,4)),
				'LevelUpTime'=>$LevelUPTime,
				'Level'=> $i,
				'LevelUpMap'=>rand(1,3),
				);
				$return = $this->oTestUser->InsertLevelUp($levelup);
			}
			$r += $return;

		}
		echo $r;
	}
}
