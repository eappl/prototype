<?php
/**
 * 管理员
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Manager.php 15233 2014-08-04 06:46:08Z 334746 $
 */

class Widget_Manager extends Base_Widget
{
	public $log = null;

	//查询权限
	const MENU_PURVIEW_SELECT = 1;

	//插入权限
	const MENU_PURVIEW_INSERT = 3;

	//更新权限
	const MENU_PURVIEW_UPDATE = 7;

	//删除权限
	const MENU_PURVIEW_DELETE = 15;

	/**
	 * 管理员表
	 * @var string
	 */
    protected $table = 'config_manager';

    /**
     * cookie名称
     * @var string
     */
    protected $cookieName = '__Base_Manager';
    //protected $cookieName = 'Bk5173Admin';

    /**
     * 登录表单链接
     * @var string
     */
    protected $loginUrl = '?ctl=login';

    /**
     * 登录认证链接
     * @var string
     */
    protected $loginAction = '?ctl=login&ac=post';

    /**
     * 退出链接
     * @var string
     */
    protected $logoutUrl = '?ctl=login&ac=logout';

    /**
     * 是否登录
     * @var boolean
     */
    protected $isLogged = null;

    /**
     * 自动初始化用户状态
     */
    public function auto()
    {
        if ($this->isLogin()) {
            $bind = array();
            $bind['last_active'] = time();
            $this->update($this->id, $bind);
        }

        $this->log = Widget_Log_Manager::getInstance();
    }

    public function ___registerUrl()
    {
    	return $this->registerUrl;
    }

    public function ___loginUrl()
    {
    	return $this->loginUrl;
    }

    public function ___logoutUrl()
    {
    	return $this->logoutUrl;
    }

    /**
     * 插入
     * @param array $bind
     * @return integer|boolean
     */
    public function insert($bind)
    {
        $insertStruct = array(
        	'id' => empty($bind['id']) ? null : $bind['id'],
            'name' => empty($bind['name']) ? '' : $bind['name'],
            'password' => empty($bind['password']) ? '' : $bind['password'],
            'menu_group_id' => empty($bind['menu_group_id']) ? 0 : $bind['menu_group_id'],
        	'data_groups' => empty($bind['data_groups']) ? 0 : $bind['data_groups'],
        	'is_partner'  => empty($bind['is_partner']) ? 0 : $bind['is_partner'],
            'last_login' => empty($bind['last_login']) ? Base_Registry::get('timestamp') : $bind['last_login'],
            'last_active' => empty($bind['last_active']) ? Base_Registry::get('timestamp') : $bind['last_active'],
            'last_ip' => empty($bind['last_ip']) ? Base_Controller_Request_Http::getInstance()->getIp() : $bind['last_ip'],
            'reg_ip' => empty($bind['reg_ip']) ? Base_Controller_Request_Http::getInstance()->getIp() : $bind['reg_ip'],
            'reg_time' => empty($bind['reg_time']) ? Base_Registry::get('timestamp') : $bind['reg_time'],
            'reset_password' => empty($bind['reset_password']) ? 0 : 1,
        );

        return $this->db->insert($this->table, $insertStruct);
    }

    /**
     * 更新
     * @param integer $id
     * @param array $bind
     * @return integer
     */
    public function update($id, $bind)
    {
        $id = intval($id);

        $preUpdateStruct = array(
            'name' => empty($bind['name']) ? '' : $bind['name'],
            'password' => empty($bind['password']) ? '' : $bind['password'],
            'menu_group_id' => empty($bind['menu_group_id']) ? 0 : $bind['menu_group_id'],
        	'data_groups' => empty($bind['data_groups']) ? 0 : $bind['data_groups'],
        	'is_partner'  => empty($bind['is_partner']) ? 0 : $bind['is_partner'],
            'last_login' => empty($bind['last_login']) ? Base_Registry::get('timestamp') : $bind['last_login'],
            'last_active' => empty($bind['last_active']) ? Base_Registry::get('timestamp') : $bind['last_active'],
            'last_ip' => empty($bind['last_ip']) ? Base_Controller_Request_Http::getInstance()->getIp() : $bind['last_ip'],
            'reg_ip' => empty($bind['reg_ip']) ? Base_Controller_Request_Http::getInstance()->getIp() : $bind['reg_ip'],
            'reg_time' => empty($bind['reg_time']) ? Base_Registry::get('timestamp') : $bind['reg_time'],
        	'reset_password' => empty($bind['reset_password']) ? 0 : 1,
        	// selena add 3013/4/8
        	'machine_show_list' => empty($bind['machine_show_list']) ? 0 : $bind['machine_show_list'],
        );

        $updateStruct = array();
        foreach ($bind as $key => $val) {
            if (array_key_exists($key, $preUpdateStruct)) {
                $updateStruct[$key] = $preUpdateStruct[$key];
            }
        }

        return $this->db->update($this->table, $updateStruct, '`id` = ?', $id);
    }

    /**
     * 根据用户名更新
     * @param string $name
     * @param array $bind
     * @return integer|boolean
     */
    public function updateByName($name, array $bind)
    {
        $preUpdateStruct = array(
            'name' => empty($bind['name']) ? '' : $bind['name'],
            'password' => empty($bind['password']) ? '' : $bind['password'],
            'menu_group_id' => empty($bind['menu_group_id']) ? 0 : $bind['menu_group_id'],
        	'data_groups' => empty($bind['data_groups']) ? 0 : $bind['data_groups'],
        	'is_partner'  => empty($bind['is_partner']) ? 0 : $bind['is_partner'],
            'last_login' => empty($bind['last_login']) ? Base_Registry::get('timestamp') : $bind['last_login'],
            'last_active' => empty($bind['last_active']) ? Base_Registry::get('timestamp') : $bind['last_active'],
            'last_ip' => empty($bind['last_ip']) ? Base_Controller_Request_Http::getInstance()->getIp() : $bind['last_ip'],
            'reg_ip' => empty($bind['reg_ip']) ? Base_Controller_Request_Http::getInstance()->getIp() : $bind['reg_ip'],
            'reg_time' => empty($bind['reg_time']) ? Base_Registry::get('timestamp') : $bind['reg_time'],
        	'reset_password' => empty($bind['reset_password']) ? 0 : 1,
        	
        );

        $updateStruct = array();
        foreach ($bind as $key => $val) {
            if (array_key_exists($key, $preUpdateStruct)) {
                $updateStruct[$key] = $preUpdateStruct[$key];
            }
        }

        return $this->db->update($this->table, $updateStruct, '`name` = ?', $name);
    }

    /**
     * 删除
     * @param integer $id
     * @return integer|boolean
     */
    public function delete($id)
    {
    	$id = intval($id);

        return $this->db->delete($this->table, '`id` = ?', $id);
    }

    /**
     * 删除
     * @param string $name
     * @return integer
     */
    public function deleteByName($name)
    {
        return $this->db->delete($this->table, '`name` = ?', $name);
    }

    public function getRow($id = 0, $fields = '*')
    {
        $id = intval($id);

        if (empty($id)) {
            $id = $this->id;
        }

        $sql = "SELECT $fields FROM {$this->table} WHERE `id` = ?";
        return $this->db->getRow($sql, $id);
    }

    public function getRowByName($name, $fields = '*')
    {
        $sql = "SELECT $fields FROM {$this->table} WHERE `name` = ?";
        $row = $this->db->getRow($sql, $name);

        return $row;
    }

    public function getLikeName($bind, $fields = '*')
    {
    	$condition=" 1=1 ";
    	$param=array();
    	if(!empty($bind['username']))
    	{
    		$param[]="%".$bind['username']."%";
    		$condition.=" and name like ? ";
    	}
    	
    	if(!empty($bind['menu_group_id']))
    	{
    		$param[]=$bind['menu_group_id'];
    		$condition.=" and menu_group_id = ? ";
    	}
    	
    	if(!empty($bind['data_group_id']))
    	{
    		$param[]="%".$bind['data_group_id']."%";
    		$condition.=" and data_groups like ? ";
    	}
    	
    	if($bind['is_partner'] == '0' || $bind['is_partner'] == '1')
    	{
    		$param[]=$bind['is_partner'];
    		$condition.=" and is_partner = ? ";
    	}
    	
        $sql = "SELECT $fields FROM {$this->table} WHERE $condition ";
        $row = $this->db->getAll($sql,$param);

        return $row;
    }
    
    public function getOne($id, $field)
    {
        $sql = "SELECT $field FROM {$this->table} WHERE `id` = ?";;
        return $this->db->getOne($sql, $id);
    }

    public function getOneByName($name, $field)
    {
        $sql = "SELECT $field FROM {$this->table} WHERE `name` = ?";
        return $this->db->getOne($sql, $name);
    }

    /**
     * 获取所有管理人员
     * @param string $fields
     * @return array
     */
    public function getAll($fields = '*')
    {
        $sql = "SELECT $fields FROM {$this->table} ";
        return $this->db->getAll($sql);
    }

    /**
     * 检查名称是否存在
     * @param string $name
     * @return boolean
     */
    public function nameExists($name)
    {
        $sql = "SELECT `id` FROM `{$this->table}` WHERE `name` = ?";
        $id = $this->db->getOne($sql, $name);
        return $id > 0;
    }

    /**
     * 登录
     * @param string $name
     * @param string $passwd
     * @param integer $expired
     * @return boolean
     */
    public function login($name, $password, $expired = 0)
    {
        $manager = $this->getRowByName($name, '`id`, `name`, `password`, `menu_group_id`, `data_groups`, `reset_password`');
        $expired = intval($expired);
        if ($manager && md5($password) == $manager['password']) {
            $cookieManager = Base_String::encode($manager['id'] . ' ' . $manager['menu_group_id'] . ' ' . $manager['data_groups'] . ' ' . $manager['name'] . ' ' . $manager['reset_password']);
            Base_Cookie::set($this->cookieName, $cookieManager, $expired);
            $bind = array();
            $bind['last_login'] = Base_Registry::get('timestamp');
            $bind['last_active'] = Base_Registry::get('timestamp');
            $bind['last_ip'] = Base_Controller_Request_Http::getInstance()->getIp();
            $this->update($manager['id'], $bind);

            unset($manager['password']);
            $this->push($manager);
            $this->isLogged = true;

            return true;
        }

        $this->isLogged = false;

        return false;
    }
    

    /**
     * 退出登录
     * @return void
     */
    public function logout()
    {
        Base_Cookie::remove($this->cookieName);
    }

    /**
     * 判断用户是否登录
     * @return boolean
     */
    public function isLogin()
    {
        if (null !== $this->isLogged)
        {
            return $this->isLogged;
        } 
        else 
        {
            $cookieManager = Base_Cookie::get($this->cookieName);
            @list($id, $menu_group_id, $data_groups, $name, $reset_password) = explode(' ', Base_String::decode($cookieManager));
            if ('' != $name && $id > 0) 
            {
                $manager = array('name' => $name, 'id' => $id, 'reset_password'=>$reset_password);
                $this->push($manager);
                $managerArr = $this->get($id);
				$this->menu_group_id = $managerArr['menu_group_id'];
				$this->data_groups = $managerArr['data_groups'];
                
                return $this->isLogged = true;
            }
            else 
            {
                $this->logout();
            }

            return $this->isLogged = false;
        }
    }
    /*
	public function isLogin()
    {
        if (null !== $this->isLogged) 
        {
            return $this->isLogged;
        }
         else 
        {
    		if(!isset($_COOKIE['Bk5173Admin']) || empty($_COOKIE['Bk5173Admin'])){
    			header("Location: http://cis.5173.com/Account/Login/?returnUrl=".urlencode("http://prototype.5173.com/"));
    			exit;
    		}   	    
	    	$sign = md5($_COOKIE['Bk5173Admin'].'_'.'534r3@#$$');
	    	$url = "http://cis.5173.com/Service/ValidateToken?token=".$_COOKIE['Bk5173Admin']."&sign=".$sign;
	    	$rs = file_get_contents($url);
	    	$result = json_decode($rs,true);   	
			$info = explode("\n",$result['Name']);
            {
                $manager = $this->getByName($info[2]);
                unset($manager['password']);
                $this->push($manager);
				$this->menu_group_id = $manager['menu_group_id'];
				$this->data_groups = $manager['data_groups'];
			}
			
	    	//$this->ask_login_name = $info[2];
            return $this->isLogged = true;
        }
    }
	*/

    /**
     * 检查菜单权限
     * @param integer $menu_id
     * @param string $operation
     * @param boolean $return
     * @return boolean
     * @throws Base_Exception
     */
    public function checkMenuPermission($operation, $return = false)
    {
		$oMenu = new Widget_Menu();
		$oMenuPurview = new Widget_Menu_Permission();
        $t = explode("&",$_SERVER['QUERY_STRING']);
        foreach($t as $key => $value)
		{
			$t2 = explode("=",$value);
			if(trim($t2[0])=="ctl")
			{
				$ctl = $value;
                break;
			}
		}
        $link = $ctl;
		//获取页面ID
		$MenuInfo = $oMenu->getOneBylink("?".$link, "name,menu_id,parent,permission_list");
		//获取当前用户组在当前页面的所有权限
        $purview = $oMenuPurview->getPermission($MenuInfo['menu_id'], $this->menu_group_id);
		//如果只是进入页面,不执行操作
		if($operation == "0")
		{
			//当前页面有任何权限
			if(count($purview)>0)
			{
				$return = array('return'=>1);
			}
			else
			{
				$return = array('return'=>0,'message'=>"对不起,您没有进入 ".$MenuInfo['name']." 的权限!");
			}
		}
		else
		{
            if(strpos($MenuInfo['permission_list'],$operation))
			{
                foreach($purview as $key => $value)
				{
                    if($value['permission'] == $operation)
					{
						$return = array('return'=>1);
						return $return;
					}
				}
				$t = explode("|",$MenuInfo['permission_list']);
				foreach($t as $k => $v)
				{
					$t2 = explode(":",$v);
					if($t2[1] == $operation)
					{
						$action = $t2[0];
						break;
					}
				}
				$return = array('return'=>0,'message'=>"对不起,您没有执行 ".$action." 的权限!");
			}
			else
			{
				$return = array('return'=>0,'message'=>"无此权限!");
			}
		}
		return $return;
		
    }

    /**
     * 取得一个管理员信息
     * @param integer $id
     * @param string $fields
     * @return boolean
     * @throws Base_Exception
     */
	public function get($id = 0, $fields = '*')
	{
		$id = intval($id);
				
		if (empty($id)) {
			$id = $this->id;
		}
		$sql = "SELECT $fields FROM {$this->table} WHERE `id` = ?";
		return $this->db->getRow($sql, $id);
	}
	public function getByName($name, $fields = '*')
	{				
		$sql = "SELECT $fields FROM {$this->table} WHERE `name` = ?";
		return $this->db->getRow($sql, $name);
	}

}
