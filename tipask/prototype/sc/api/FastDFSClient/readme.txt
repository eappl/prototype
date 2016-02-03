例子请参见demo.php
除了该文件，其它文件为客户端正式代码，请放置在引用库下对应FastDFS Client的譬如fdfsClient目录下

使用前需进行相关配置：

本客户端的配置文件为fdfs_config.php:
1、需要配置DFS server 的tracker server列表，其属性有ip、port、group_name、sock使用默认值-1
2、需配置DFS server的下载http的URL参数：
	storager_ip--storager server的ip；
	storager_http_port--storager server的http port，供下载用；
	其它参数使用默认值；

本客户端使用了log4php：
1、在FastDFSClient.php注意require的路径配置；
2、配置文件log4php.properties：
	注意日志文件的路径，使用绝对路径
	配置日志级别，DEBUG, INFO...，具体可参见log4php的相关文档
3、请将log4php放置在引用库下
4、若不使用log4php，可修改FastDFSClient.php代码



