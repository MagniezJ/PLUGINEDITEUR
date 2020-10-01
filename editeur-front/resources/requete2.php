<?php
 if(!isset($_SESSION)){
    session_start();
}

global $wpdb;
$path = preg_replace('/wp-content(?!.*wp-content).*/','',__DIR__);
include($path.'wp-load.php');

$r=$wpdb->update("wp_posts",  ["post_title"=>"TEST"], ["ID"=>'189']);

?>