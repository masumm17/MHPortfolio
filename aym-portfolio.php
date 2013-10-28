<?php
if(!defined('ABSPATH')){
    exit('Not Allowed');
}
if(!defined('AYM_PORTFOLIO_PATH')){
    define('AYM_PORTFOLIO_PATH', get_template_directory().'/portfolio');
    define('AYM_PORTFOLIO_URI', get_template_directory_uri().'/portfolio');
}
if(is_admin()){
    include AYM_PORTFOLIO_PATH.'/inc/aym-admin.php';
}
add_action('init', 'aymsc_create_cst_portfolio');
function aymsc_create_cst_portfolio(){
    //Custom Taxonomies[Portfolio Categories]
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
      'name' => _x( 'Portfolio Categories', 'taxonomy general name' ),
      'singular_name' => _x( 'Portfolio Category', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search Portfolios Categories' ),
      'all_items' => __( 'All Portfolios Categories' ),
      'parent_item' => __( 'Parent Portfolios Category' ),
      'parent_item_colon' => __( 'Parent Portfolios Category:' ),
      'edit_item' => __( 'Edit Portfolios Category' ), 
      'update_item' => __( 'Update Portfolios Category' ),
      'add_new_item' => __( 'Add New Portfolios Category' ),
      'new_item_name' => __( 'New Portfolios Category' ),
      'menu_name' => __( 'Categories' ),
    ); 	
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_ui' => true,
        'show_tagcloud' => false,
        'show_admin_column' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'portfolios_category', 'with_front' => '' )
    );

    register_taxonomy('portfolio_category',array('portfolio'), $args);
    $args = NULL;
    $labels = NULL;
    
    //Custom Taxonomies[Portfolio Tags]
    // Add new taxonomy, make it non-hierarchical (like post's Tags)
    $labels = array(
      'name' => _x( 'Portfolio Tags', 'taxonomy general name' ),
      'singular_name' => _x( 'Portfolio Tag', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search Portfolios Tags' ),
      'all_items' => __( 'All Portfolios Tags' ),
      'edit_item' => __( 'Edit Portfolios Tags' ), 
      'update_item' => __( 'Update Portfolios Tags' ),
      'add_new_item' => __( 'Add New Portfolios Tag' ),
      'new_item_name' => __( 'New Portfolios Tag' ),
      'menu_name' => __( 'Tags' ),
    ); 	
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_ui' => true,
        'show_tagcloud' => true,
        'show_admin_column' => true,
        'hierarchical' => false,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'portfolios_tags', 'with_front' => '' )
    );

    register_taxonomy('portfolio_tags',array('portfolio'), $args);
    $args = NULL;
    $labels = NULL;
    
    
    //Custom post type[Projects]
    $labels = array(
        'name' => _x('Portfolios', 'post type general name'),
        'singular_name' => _x('Portfolio', 'post type singular name'),
        'add_new' => _x('Add New', 'Portfolio'),
        'add_new_item' => __('Add New Portfolio'),
        'edit_item' => __('Edit Portfolio'),
        'new_item' => __('New Portfolio'),
        'all_items' => __('All Portfolios'),
        'view_item' => __('View Portfolio'),
        'search_items' => __('Search Portfolios'),
        'not_found' =>  __('No Portfolios found'),
        'not_found_in_trash' => __('No Portfolio found in Trash'), 
        'parent_item_colon' => '',
        'menu_name' => __('Portfolios')
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true, 
        'show_in_nav_menus' => true,
        'show_in_menu' => true, 
        'query_var' => true,
        'rewrite' => array( 'slug' => 'portfolio', 'with_front' => '' ),
        'capability_type' => 'post',
        'has_archive' => true, 
        'taxonomies' => array('portfolio_category', 'portfolio_tags'),
        'hierarchical' => false,
        //'menu_position' => null,
        'supports' => array( 'title', 'editor', 'thumbnail', 'revisions')
    ); 
    register_post_type('portfolio', $args);
    $args = NULL;
    $labels = NULL;
}
//Flush rewrite rules
function aympf_rewrite_flush() {
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'aympf_rewrite_flush' );

//Add scripts to front end
add_action( 'wp_enqueue_scripts', 'aymp_add_js' );
function aymp_add_js() {
    if ( aymp_has_shortcode( 'portfolio_gallery' ) ) {
        wp_enqueue_script( 'aympj-easing', AYM_PORTFOLIO_URI . '/js/jquery.easing.1.3.js', array('jquery') );
        wp_enqueue_script( 'aymp-esmartzoom', AYM_PORTFOLIO_URI . '/js/e-smart-zoom-jquery.min.js', array('jquery') );
        wp_enqueue_script( 'aymLBplus', AYM_PORTFOLIO_URI . '/js/jquery.aymLBplus.js', array('jquery','aympj-easing','aymp-esmartzoom') );
        
        wp_enqueue_style( 'portflio_gallery', AYM_PORTFOLIO_URI . '/css/aymlib.css' );
    }
}
/**
 * Check posts to see if shortcode has been used
 *
 * @since 1.0.0
 */
function aymp_has_shortcode( $shortcode = '' ) {
    global $wp_query;
    foreach( $wp_query->posts as $post ) {
        if ( ! empty( $shortcode ) && stripos($post->post_content, '[' . $shortcode) !== false ) {
            return true;
        }
    }
    return false;
}
 

//Add shortcodes
add_shortcode('portfolio_gallery', 'aymp_portfolio_gallery');
function aymp_portfolio_gallery( $atts, $content = null ) {
    $atts = shortcode_atts( array(
            'cat' => '',
            'include_children' => '',
            'tag' => '',
            'relation' => 'AND',
            'num' => -1,
            'order' => 'DESC',
            'orderby' => 'date',
            'show_projects_nav' => 'true',
            'show_photos_nav' => 'true',
            'background_color' => '#191919'
    ), $atts, 'portfolio_gallery' );
    
    $query_args = array();
    $query_args['post_type'] = 'portfolio';
    if($atts['num'] > 0){
        $query_args['posts_per_page'] = $atts['num'];
    }else{
        $query_args['posts_per_page'] = -1;
    }
    
    //Taxonomi Query[category and tags]
    $tax_query = array();
    
    //Get category choice
    if($atts['cat']){
        $cats = explode(',', $atts['cat']);
        $cats_ID = array();
        foreach ($cats as $val){
            $cats_ID[] = (int)trim($val);
        }
        if(!empty($cats_ID)){
            if(!empty($atts['include_children']) && $atts['include_children'] != 'false'){
                $atts['include_children'] = true;
            }else{
                $atts['include_children'] = false;
            }
            $tax_query[] = array(
                'taxonomy' => 'portfolio_category',
                'field' => 'id',
                'terms' => $cats_ID,
                'include_children' => $atts['include_children']
            );
        }
    }
    //Get tag choice
    if($atts['tag']){
        $tag= explode(',', $atts['tag']);
        $tags_ID = array();
        foreach ($tag as $val){
            $tags_ID[] = (int)trim($val);
        }
        if(!empty($cats_ID)){
            $tax_query[] = array(
                'taxonomy' => 'portfolio_tags',
                'field' => 'id',
                'terms' => $tags_ID
            );
        }
    }
    
    //Get cat and tag relation
    if(!empty($tax_query)){
        if(!empty($atts['relatlion'])){
            $atts['relatlion'] = strtoupper($atts['relatlion']);
        }else{
            $atts['relatlion'] = 'OR';
        }
        if(in_array($atts['relatlion'], array('AND', 'OR')) && !empty($tags_ID) && !empty($cats_ID) ){
            $tax_query[] = $atts['relatlion'];
        }
        $query_args['tax_query'] = $tax_query;
    }
    
    //Get order
    if(!empty($atts['order'])){
        $query_args['order'] = $atts['order'];
    }
    //Get orderby
    if(!empty($atts['orderby'])){
        $query_args['orderby'] = $atts['orderby'];
    }
    //Get Portfolio Items
    $portfolio_items = get_posts($query_args);
    
    $phtml = '';
    if($portfolio_items){
        $galleryID = 'aymLB-'. rand(100, 999) .rand(1111, 9999);
        ob_start();
?> 
<div class="aymLB clearfix" id="<?php echo $galleryID; ?>" data-porfoliourl="<?php echo AYM_PORTFOLIO_URI ?>">
    <?php foreach($portfolio_items as $pitem){
        $client_name = get_post_meta($pitem->ID, 'aymp_client_name', true);
        $website = get_post_meta($pitem->ID, 'aymp_website', true);
        $screenshots = get_post_meta($pitem->ID, 'aymp_screenshots', true);
        $screenshots = !empty($screenshots)? (array)$screenshots:array();
        
    ?> 
    <div class="singleProject">
        <a href="#" class="aym-trig aym-trig2 ProjectThumb"><?php echo get_the_post_thumbnail($pitem->ID, 'plisting-thumb', array('alt' =>  esc_attr($pitem->post_title))); ?></a>
        <div style="display: none;" class="ProjectsDesc">
            <h2 class="project-title"><?php echo $pitem->post_title; ?></h2>
            <div class="project-meta">
                <?php if(!empty($client_name)){ ?><p class="client-name"><strong>Client Name: </strong><?php echo $client_name;?></p><?php }?> 
                <?php if(!empty($website)){ ?><p class="client-name"><strong>Website: </strong><a target="_blank" title="" href="<?php echo $website;?>"><?php echo $website;?></a></p><?php }?> 
            </div>
            <div class="project-description">
                <?php
                echo apply_filters( 'the_content', force_balance_tags($pitem->post_content ));
                ?>
            </div>
        </div>
        <div style="display: none;" class="ProjectPhoto">
            <?php foreach($screenshots as $sc_id){
                $src  = wp_get_attachment_image_src( $sc_id, 'full' );
                $pp_full = $src[0];

                $src  = wp_get_attachment_image_src( $sc_id, 'plisting-thumb' );
                $pp_thumb = $src[0];
            ?>
            <a href="<?php echo $pp_full;?>"><img alr="" src="<?php echo $pp_thumb;?>" /></a>
            <?php }?>
        </div>
    </div>
    
    <?php }?>
    
</div>
<script type="text/javascript">
    (function($){
        $('#<?php echo $galleryID; ?>').aymLBplus({
            'backgroundColor': '<?php echo $atts['background_color']; ?>',
            'showProjectsNav': <?php echo $atts['show_projects_nav']; ?>,
            'showPhotosNav': <?php echo $atts['show_photos_nav']; ?>
        });
    })(jQuery);
</script>
<?php

        $phtml = ob_get_clean();
    }
    return $phtml;
}