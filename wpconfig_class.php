<?php

class WpConfig{
    static $loaded = false;
    
    public static function getJSon(){
	if(!self::$loaded){
	    self::initWP();
	}
	return json_encode(self::getDataArray());
    }
    
    public static function getSecuredKeys(){
	if(!self::$loaded){
	    self::initWP();
	}
	return array(esc_attr( get_option('publicKey')) => esc_attr( get_option('privateKey')));
    }
    
    private static function initWP(){
	require_once('../../../wp-config.php');
	if (!function_exists( 'get_plugins' )) {
	    require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	
	//Chargement des librairies de base WP
	require_once('../../../wp-config.php');
	if (!function_exists( 'get_plugins' )) {
	    require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	
	self::$loaded = true;
    }
    
    private static function getDataArray(){
	$output = array();
	$output['cms'] = self::getCms();
	$output['plugins'] = self::getPlugins();
	
	return $output;
    }
    
    private static function getCms(){
	$output = array();
	
	$output['name'] = 'wordpress';
	$output['version'] = get_bloginfo('version');
	
	if(!$output['version']){
	    require(ABSPATH. '/wp-includes/version.php');
	    $output['version'] = $wp_version;
	}

	return $output;
    }
    
    private static function getPlugins(){
	$output = array();
	
	$all_plugins = get_plugins();

	foreach($all_plugins as $key => $plugin){
	    $line = array();
	    $line['type'] = 'plugin';
	    $line['name'] = $plugin['Name'];
	    $line['version'] = $plugin['Version'];
	    $line['editeur'] = $plugin['Author'];
	    $line['pluginUrl'] = $plugin['PluginURI'];
	    $line['info'] = $plugin['Description'];
	    $line['enabled'] = (is_plugin_active($key)) ? true : false;
	    $output[] = $line;
	}
	
	return $output;
    }
}