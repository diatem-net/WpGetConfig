<?php
/**
 * Plugin Name: Diatem WPGetContent
 * Plugin URI: 
 * Description: Implémente un service REST sécurisé permettant de récupérer à distance la version de WP ainsi que des modules utilisés. Services accessibles avec l'Url [UrlSite]wp-content/plugins/diatem_wpgetconfig/ en GET. Clés d'accès configurées dans Réglages/WpGetConfig : accès REST sécurisé
 * Version: 0.1.0
 * Author: Diatem
 * Author URI: http://www.diatem.net/
 * License: GPL v2
 */
 
//gestion interface WP
add_action('admin_menu', 'register_admininterface');
add_action('admin_init', 'register_settings' );

function register_admininterface(){
    add_options_page('Diatem WpGetConfig : configuration de l\'accès REST sécurisé', 'WpGetConfig : accès REST securisé', 'manage_options', 'diatem-wpgetconfig-restsecuredkeys', 'admin_callRestSecuredKeys');
}

function register_settings(){
    register_setting('wpgetconfig-group', 'publicKey');
    register_setting('wpgetconfig-group', 'privateKey');
}

function admin_callRestSecuredKeys() {
    include 'admin/restsecuredkeys.php';
}