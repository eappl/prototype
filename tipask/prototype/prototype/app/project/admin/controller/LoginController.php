<?php
/**
 * 管理员登录相关处理
 * @author Chen <cxd032404@hotmail.com>
 * $Id: LoginController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class LoginController extends AbstractController
{

	/**
	 * 是否需要登录后访问
	 * @var boolean
	 */
	protected $needLogin = false;

    /**
     * 登录表单
     */
    public function indexAction()
    {
        $referer = $this->request->getReferer();
        if (empty($referer)) {
            $referer = '?';
        }

        include $this->tpl();
    }

    /**
     * 登录认证
     */
    public function postAction()
    {
        $name = $this->request->name;
        $passwd = $this->request->passwd;
        $referer = $this->request->referer;
        $isLogin = $this->manager->login($name, $passwd);
        if ($isLogin) {
            $this->response->redirect($referer);
        } else {
            $passwd = substr($passwd, 0, 3) . '*' . substr($passwd, -2);
            $is_error = 1;
            $this->response->redirect('?ctl=login&error=1');
        }
    }
    
    public function loginAction()
    {
    	$this->postAction();
    }

    /**
     * 退出
     */
    public function logoutAction()
    {
    	$this->manager->logout();
      $this->response->redirect('?ctl=login');
    }

}
