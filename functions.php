<?php
define('THEME_DIR', get_stylesheet_directory());
define('THEME_URI', get_stylesheet_directory_uri());

add_filter( 'get_pages', function ( $pages, $args )
{
    // First make sure this is an admin page, if not, bail
    if ( !is_admin() )
        return $pages;

    // Make sure that we are on the reading settings page, if not, bail
    global $pagenow;
    if ( 'options-reading.php' !== $pagenow )
        return $pages;

    // Remove the filter to avoid infinite loop
    remove_filter( current_filter(), __FUNCTION__ );

    // Setup our static counter
    static $counter = 0;

    // Bail on the third run all runs after this. The third run will be 2
    if ( 2 <= $counter )
        return $pages;

    // Update our counter
    $counter++;

    $args = [
        'post_type'      => 'contractor_page',
        'posts_per_page' => -1
    ];
    // Get the post type posts with get_posts to allow non hierarchical post types
    $new_pages = get_posts( $args );    

    /**
     * You need to decide if you want to add custom post type posts to the pages
     * already in the dropdown, or just want the custom post type posts in
     * the dropdown. I will handle both, just remove what is not needed
     */
    // If we only need custom post types
    $pages = $new_pages;

    // If we need to add custom post type posts to the pages
    // $pages = array_merge( $new_pages, $pages );

    return $pages;
}, 10, 2 );

add_action( 'pre_get_posts', function ( $q )
{
    if (    !is_admin() // Only target the front end
         && $q->is_main_query() // Only target the main query
         && 'page' === get_option( 'show_on_front' ) // Only target the static front page
         && $q->is_home()
    ) {
        $q->set( 'post_type', 'contractor_page' );
    }
});

if(class_exists('ActionScheduler')) {
	require_once THEME_DIR.'/inc/action-scheduler-hooks.php';
}

require_once THEME_DIR.'/inc/class-theme.php';

\HomeViet\Theme::instance();