<?php

global $wpdb;

$resultats = $wpdb->get_results("SELECT * FROM wp_posts 
where ID='<script type="text/javascript">document.write(id);</script>;'") ;

echo->$resultats;