<?php
/**
 * linux下进程管理类。
 * 
 * $Id: 
 * @category   Cron_Process
 * @Description
 * @author   陈晓东
 * @version
 */

/* An easy way to keep in track of external processes.
 * Ever wanted to execute a process in php, but you still wanted to have somewhat controll of the process ? Well.. This is a way of doing it.
 * @compability: Linux only. (Windows does not work).
 * @author: Peec
 ps aux | grep '/usr/local/php/bin/php /home/scheduler/scheduler.php' | grep -v grep
 */
class Widget_Task
{
    private $pid;
    private $cmd;

    /*
     * 启动命令行进程
     * 参数$command：命令行字符串，可选
     * 返回值：进程ID
     * 
     */
    public function run($command='')
    {
    	if(!empty($command))
    		$this->cmd = $command;
    		
    	if(empty($this->cmd))
    		return 0;
    		
        $command = "nohup {$this->cmd} >> /dev/null & echo $!";
        exec($command ,$op);
        $this->pid = intval($op[0]);
        
        echo "$$command\r\n";
        echo implode("\r\n",$op)."\r\n";
        
        return $this->pid;
    }
    
    /*
     * 重启一个进程
     * 返回值：BOOL,成功返回TRUE否则返回失败
     */
    public function reset()
    {
    	//如果正在运行，那么停止
    	if($this->status())
    		$this->stop();
    	//如果停止失败，返回执行失败
    	if($this->status())
    		return false;
    		
    	//启动
    	return $this->run();
    }
    
    /*
     * 启动命令行进程，已经使用该类启动则不再启动
     * 返回值：成功返回TRUE否则返回FALSE
     */
    public function runonce($command='')
    {
    	if(!empty($command))
    		$this->cmd = $command;
    		
    	if(empty($this->cmd))
    		return false;
    		
    	if($this->status())
	    	return false;
	    	
	    return $this->run();
    } 

    /*
     * 停止、杀掉命令行进程
     * 参数：无
     * 返回值：返回任务状态
     */
    public function stop()
    {
        $command = "kill {$this->pid}";
        exec($command ,$op);
        
        echo "$$command\r\n";
        echo implode("\r\n",$op)."\r\n";
        
        return $this->status();
    }

    /*
     * 进程状态
     * 返回值：BOOL ，进程存在时返回TRUE否则返回FALSE
     * 
     */
    public function status()
    {
		if(empty($this->pid))
			return false;
			
        $command = "ps -p {$this->pid}";
        exec($command,$op);
        
        if(isset($op[1]))
        	return !empty($op[1]);
        
        return false;
    }
    //当前进程PID
    public function pid()
    {
        return $this->pid;
    }
    
    /*
     * 找出与自己相同命令行的进程
     * 返回值：array，数组中保存与自己相同的进程命令行
     */    
    public function searchsame()
    {
		$command = "ps -ef|grep '{$this->cmd}' | grep -v grep";
		exec($command,$op);
        
        return $op;
    }

}
?>