<?php
/**
 * Template Name: Nhân sự
 * 
 */

get_header();
while (have_posts()) {
	the_post();
	the_content();
}
get_footer();