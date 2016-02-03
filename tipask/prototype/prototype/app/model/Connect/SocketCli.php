<?php
/**
 * Socket客户端函数
 * @author 张骥 <344505721@qq.com>
 */


class Connect_SocketCli extends Base_Widget
{
	protected $host;//连接socket的主机
	protected $port;//socket的端口号 
	protected $error=array();
	protected $socket=null;//socket的连接标识
	protected $queryStr="";//发送的数据
    
	public function SetSocket($host,$port) {
		$this->host=$host;
		$this->port=$port;
		$this->CreateSocket();//创建连接
	}
	
	//创建socket
	public function CreateSocket(){
		!$this->socket&&$this->socket=socket_create(AF_INET, SOCK_STREAM, SOL_TCP);//创建socket
		$r=@socket_connect($this->socket,$this->host,$this->port);
		if($r){
			return $r;
		}else{
			$this->error[]=socket_last_error($this->socket);
			return false;
		}
	}
	
	//向socket服务器写入数据并读取
	public function wr($contents){
		$this->queryStr="";
		$this->queryStr=$contents;
		!$this->socket&&$this->CreateSocket();
		$contents=$this->fliterSendData($contents);
		$result=socket_write($this->socket,$contents,strlen($contents));
		if(!intval($result)){
			$this->error[]=socket_last_error($this->socket);
			return false;
		}
        
		$response=socket_read($this->socket,1024);
        
		if(false===$response){
			$this->error[]=socket_last_error($this->socket);
			return false;
		}
		return $response;
	}
	
	
	//对发送的数据进行过滤
	public function fliterSendData($contents){
		//对写入的数据进行处理
		return $contents;
	}	
	
	//所有错误信息 
	public function getError(){
		return $this->error;
	}
	
	//最后一次错误信息
	public function getLastError(){
		return $this->error(count($this->error));
	}
    
	//获取最后一次发送的消息
	public function getLastMsg(){
		return $this->queryStr;
	}
	
	public function getHost(){
		return $this->host;
	}
	
	public function getPort(){
		return $this->port;
	}
	
	//关闭socket连接
	public function close(){
		$this->socket&&socket_close($this->socket);//关闭连接
		$this->socket=null;//连接资源初始化
	}
}