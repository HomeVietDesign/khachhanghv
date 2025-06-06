<?php
/**
 * Template Name: Hợp đồng
 * 
 */
global $current_client;

get_header();
while (have_posts()) {
	the_post();
	the_content();
}
get_footer();