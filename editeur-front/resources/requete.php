<?php
 if(!isset($_SESSION)){
    session_start();
}
 
global $wpdb;
$path = preg_replace('/wp-content(?!.*wp-content).*/','',__DIR__);
include($path.'wp-load.php');

$result=$wpdb->get_var("SELECT post_title FROM wp_posts WHERE ID= ".$_SESSION["ID180"]."");
$r=$wpdb->get_var("SELECT post_content FROM wp_posts WHERE ID=".$_SESSION["ID180"]."");
/* $id = get_post($_SESSION["ID180"])->post_author;
$name=get_the_author( 'display_name', $id ); */
$query = array($result,$r);
    echo json_encode($query);
?>