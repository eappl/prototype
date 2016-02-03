<?php
/**
 * 
 * Project Name:      SEARCH5173_FastDFS_Client
 *
 * Author:            302498
 * Create Date:       2013-03-19
 * Remark:            
 */

return array(
	
		/**
		* change me to correct tracker server list, assoc array element:
		*    ip_addr: the ip address or hostname of the tracker server
		*    port:    the port of the tracker server
		*    group_name: 
		*    sock:    the socket handle to the tracker server, should init to -1 or null
		*/
			'tracker_servers' => array(
				array(
						'ip_addr' => '192.168.130.20',
						'port' => 22124,
						'group_name' => 'sk',
						'sock' => -1)
		
		
		),
		
		'fdfs_http_params' => array(
				'storage_server' => '192.168.130.21:8888',
				'anti_steal_token' => FALSE,
				'secret_key' => 'FastDFS1234567890'),
		
		'fdfs_network_timeout' => 30 //second
	);
?>