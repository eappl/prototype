<?php 
$CacheConfig = require(dirname(dirname(__FILE__))."/CommonConfig/cacheConfig.php");
$CommonConfig = require(dirname(dirname(__FILE__))."/CommonConfig/commonConfig.php");
$Config =  array(
		'MEMECACHE_SERVER' => $CacheConfig['MEMECACHE_SERVER'],
		'MEMECACHE_PORT'   => $CacheConfig['MEMECACHE_PORT'],
		
		'REDIS_SERVER' => $CacheConfig['REDIS_SERVER'],
		'REDIS_PORT'   => $CacheConfig['REDIS_PORT'],

		'DATA_KEY'	  => $CommonConfig['DATA_KEY'],
		'SOLR_DOMAIN' => $CommonConfig['SOLR_DOMAIN'],
		'SOLR_PORT'   => $CommonConfig['SOLR_PORT'],
		'SOLR_SERVER' => $CommonConfig['SOLR_SERVER'],
		'SOLR_SEARCH_SERVER' => $CommonConfig['SOLR_SEARCH_SERVER'],
		'ScUrl' => $CommonConfig['ScUrl'],
		'ScadminUrl' => $CommonConfig['ScadminUrl'],
		'ScappUrl' => $CommonConfig['ScappUrl'],
);
return $Config;
?>