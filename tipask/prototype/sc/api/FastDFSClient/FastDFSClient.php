<?php
/**
 * 
 * Project Name:      SEARCH5173_FastDFS_Client
 *
 * Author:            302498
 * Create Date:       2013-03-19
 * Remark:            
 */

//require_once dirname(__FILE__).'/lib/log4php/Logger.php';
require_once dirname(__FILE__).'/fdfs_common.php';
require_once dirname(__FILE__).'/fdfs_tracker_client.php';
require_once dirname(__FILE__).'/fdfs_storage_client.php';

class FastDFSClient {
	
	//private static $logger;
	
	private $config =   array(
			'maxSize' => -1,    // 上传文件的最大值			
			'allowExts'=> array(),    // 允许上传的文件后缀 留空不作后缀检查
			'savePath' => ''// 上传文件保存路径		
	);
	
	// 错误信息
	private $error = '';
	
	public function __get($name){
		if(isset($this->config[$name])) {
			return $this->config[$name];
		}
		return null;
	}
	
	public function __set($name,$value){
		if(isset($this->config[$name])) {
			$this->config[$name]    =   $value;
		}
	}
	
	public function __construct() {
		//Logger::configure(dirname(__FILE__)."/log4php.properties");
		//self::$logger = Logger::getLogger("FastDFSClient");
	}	
	
	/**
	 * 
	 * Upload local file to DFS server
	 * @param $group_name: if it is empty, DFS server will use load balance 
	 * @param $local_file_name
	 * @return -1 mark error
	 */
	public function upload($group_name,$local_filename='') {
		if($local_filename == ''){
			$local_filename = $this->checkFile($_FILES);
			if(false === $local_filename) return -1;
		}				
		try{
			$tracker_server = tracker_get_connectionByGroupName($group_name);
			if ($tracker_server == false) {
				@unlink($local_filename);				
				runlog('api_fastDFS_log',"tracker_get_connection fail!");
				//self::$logger->error("tracker_get_connection fail!") ;				
				$this->error ='上传文件失败';
				return -1;
			}
			$storage_server = null;
			
			$meta_list = array();
			$result = storage_upload_by_filename($tracker_server, $storage_server,
				$local_filename, $meta_list,
				$group_name, $remote_filename);
			if ($result == 0) {
				@unlink($local_filename);
				$fdfs_http_params = $GLOBALS['fdfs_http_params'];
				return $this->getRemoteFilePath($remote_filename, $fdfs_http_params);
			} else {
				runlog('api_fastDFS_log',"storage_upload_by_filename fail, result=$result");
				//self::$logger->error("storage_upload_by_filename fail, result=$result");
			}
		} catch(Exception $e) {
			//self::$logger->error($e);			
		} 
		fdfs_quit($tracker_server);
		tracker_close_all_connections();		
		$this->error ='上传文件失败';
		@unlink($local_filename);
		return -1; // mark error
	}
	
	function delete($group_name,$filename){
		$file_arr = parse_url($filename);
		$filename = substr($file_arr['path'],1);
		try{
			$tracker_server = tracker_get_connectionByGroupName($group_name);
			if ($tracker_server == false) {
				runlog('api_fastDFS_log',"tracker_get_connection fail!");
				return -1;
			}
			$storage_server = null;
				
			$result = storage_delete_file($tracker_server,$storage_server,
					$group_name,$filename);
			if ($result == 0) {
				return 0;//删除成功
			} else {
				runlog('api_fastDFS_log',"storage_delete_file fail, result=$result");				
			}
		} catch(Exception $e) {
			return -1;//删除失败
		}
		return -1;//删除失败		
	}
	
	/**
	 * 
	 * Get remote file url
	 * @param $remote_filename: file path dfs server returned
	 * @param $fdfs_http_params: http config params
	 */
	public function getRemoteFilePath($remote_filename, $fdfs_http_params){
		/*
		$idx = strpos($remote_filename, "/M00/");
		$path = substr($remote_filename, $idx+4);
		$ip = $fdfs_http_params["storager_ip"];
		$port = $fdfs_http_params["storager_http_port"];
		$url = "http://".$ip.":".$port."/".$path;
		*/
		$path = substr($remote_filename, 4);
		$storage_server = $fdfs_http_params["storage_server"];
		$url = "http://".$storage_server."/".$path;
		
		return $url;
	}
	
	private function checkFile($file){
		$file = current($file);
		$pathinfo = pathinfo($file['name']);
		$file['extension'] = $pathinfo['extension'];
		//检查文件大小
		if(!$this->checkSize($file['size'])) {
			$this->error = '上传文件大小不符！';
			return false;
		}
		
		//检查文件类型
		if(!$this->checkExt($file['extension'])) {
			$this->error ='上传文件类型不正确';
			return false;
		}
		
		$saveName = $this->getSaveName($file['extension']);
		if(false === $saveName) return false;
		
	    if(!move_uploaded_file($file['tmp_name'], $this->autoCharset($saveName,'utf-8','gbk'))) {
            $this->error = '文件上传保存错误！';
            return false;
        }
        
		return $saveName;
	}
	
    private function checkSize($size) {
        return !($size > $this->maxSize) || (-1 == $this->maxSize);
    }
    
    private function checkExt($ext) {
    	$allowExts = $this->allowExts;
    	if(!empty($allowExts))
    		return in_array(strtolower($ext),$this->allowExts,true);
    	return true;
    }
    
    private function getSaveName($extension,$savePath='') {
    	//如果不指定保存文件名，则由系统默认
    	if(empty($savePath))
    		$savePath = $this->savePath;
    	// 检查上传目录
    	if(!is_dir($savePath)) {
    		// 检查目录是否编码后的
    		if(is_dir(base64_decode($savePath))) {
    			$savePath	=	base64_decode($savePath);
    		}else{
    			// 尝试创建目录
    			if(!mkdir($savePath)){
    				$this->error  =  '上传目录'.$savePath.'不存在';
    				return false;
    			}
    		}
    	}else {
    		if(!is_writeable($savePath)) {
    			$this->error  =  '上传目录'.$savePath.'不可写';
    			return false;
    		}
    	}
    	$saveName = $savePath.time().".".$extension;
    	return $saveName;
    }
    
    public function getErrorMsg() {
    	return $this->error;
    }
    
    // 自动转换字符集 支持数组转换
    private function autoCharset($fContents, $from='gbk', $to='utf-8') {
    	$from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    	$to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    	if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
    		//如果编码相同或者非字符串标量则不转换
    		return $fContents;
    	}
    	if (function_exists('mb_convert_encoding')) {
    		return mb_convert_encoding($fContents, $to, $from);
    	} elseif (function_exists('iconv')) {
    		return iconv($from, $to, $fContents);
    	} else {
    		return $fContents;
    	}
    }
}
?>