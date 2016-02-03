<?php
/**
 * 测试用户数据生成
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Connect_SqlServer extends Base_Widget
{
	//声明所用到的表

	public function GetOdbcSqlServer($Ip,$User,$Pass,$Db)
	{
		$connstr = "Driver={SQL Server};Server=$Ip;Database=$Db";
		echo $connstr."\n";
		$con=odbc_connect($connstr,$User,$Pass,SQL_CUR_USE_ODBC) or False;
		return $con;
	}
	public function CloseOdbcSqlServer($con)
	{
		return odbc_close($con);
	}
	public function getKOPUserData($act_id,$con)
	{
		$sql="select * from dbo.account where act_id = $act_id";
		$result = odbc_exec($con,$sql);
		while(odbc_fetch_row($result))
		{
			$return = array(
			'UserAccount'=>odbc_result($result, "act_name")
			);
		}
		return $return;
	}
	public function getKOSUserData($acc_uin,$con)
	{
		$oUserData = new UserData_User();
		$oGameData = new Game_Game();
		$oSourceDetail = new Config_SourceDetail();
		$sql="select * from dbo.account_info where acc_uin = $acc_uin";
		$result = odbc_exec($con,$sql);

		while(odbc_fetch_row($result))
		{
			$UserAccount = odbc_result($result, "acc_name");
		  if(substr($UserAccount,0,5)=='robot')
		  {
		  	$DirtyUser = 1;	
		  }
		  else
		  {
		  	$DirtyUser = 0;		
		  }
			$SourceDetail = 9999;
			$SourceDetailData = $oSourceDetail->getRow($SourceDetail);
			$return = array(
	  	'UserAccount'=>odbc_result($result, "acc_name"),
	  	'UserRegTime'=>strtotime(odbc_result($result, "acc_createtime")),
	  	'UserLastLogin'=>strtotime(odbc_result($result, "acc_logintime")),
	  	'UserSourceDetail'=>$SourceDetail,
			'UserSourceType'=>$SourceDetailData['SourceTypeId'],
			'UserSource'=>$SourceDetailData['SourceId'],
			'DirtyUser'=>$DirtyUser,
			);
		}
		return $return;
	}
	public function getKOSUserDataByName($acc_name,$con)
	{
		$oUserData = new UserData_User();
		$oGameData = new Game_Game();
		$oSourceDetail = new Config_SourceDetail();
		$sql="select * from dbo.account_info where acc_name = '".$acc_name."'";
		$result = odbc_exec($con,$sql);

		while(odbc_fetch_row($result))
		{
			$return = array(
	  	'UserAccount'=>odbc_result($result, "acc_name"),
	  	'UserRegTime'=>strtotime(odbc_result($result, "acc_createtime")),
	  	'UserLastLogin'=>strtotime(odbc_result($result, "acc_logintime")),
			);
		}
		return $return;
	}
	public function getKOSCharacterData($chr_uid,$con)
	{
		$sql="select * from dbo.chr_info where chr_uid = $chr_uid";
		$result = odbc_exec($con,$sql);
		while(odbc_fetch_row($result))
		{
		  $return = array(
		  	'CharacterId'=>odbc_result($result, "chr_uid"),
		  	'CharacterName'=>iconv("GB2312","UTF-8",odbc_result($result, "chr_name")),
		  	'CharacterJob'=>odbc_result($result, "chr_job"),
		  	'CharacterLevel'=>odbc_result($result, "chr_level"),
		  	'CharacterFace'=>odbc_result($result, "chr_face"),
		  	'CharacterHair'=>odbc_result($result, "chr_hair"),
		  	'CharacterTitle'=>odbc_result($result, "chr_title"),
		  	'CharacterMoney'=>odbc_result($result, "chr_money"),
		  	'CharacterMoneyBank'=>odbc_result($result, "chr_money_bank"),
		  	'CharacterMoneyTotal'=>odbc_result($result, "chr_money_bank")+odbc_result($result, "chr_money"),
		  	'CharacterExp'=>odbc_result($result, "chr_exp"),
		  	'CharacterSex'=>(odbc_result($result, "chr_sex")==1)?1:-1,
		  	'CreateTime'=>strtotime(odbc_result($result, "chr_create_date")),
		  	);
		}
		return $return;
	}
	public function getKOSUserByCharacter($chr_uid,$con)
	{
		$sql="select * from dbo.chr_info where chr_uid = $chr_uid";
		$result = odbc_exec($con,$sql);
		while(odbc_fetch_row($result))
		{
			$return = array(
			'UserId'=>odbc_result($result, "chr_uin")
			);
		}
		return $return;
	}
}
