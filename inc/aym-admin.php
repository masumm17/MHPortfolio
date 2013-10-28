<?php
/**
 * Add post metabox
 */
add_action('add_meta_boxes', 'aym_portfolio_metaboxes');
function aym_portfolio_metaboxes(){
    global $post;
    if( !$post || empty($post->post_type) || 'portfolio' != $post->post_type){
        return;
    }
    //Add required styles & scripts
    add_action('admin_enqueue_scripts', 'aym_portfolio_metabox_scripst');
    
    // Add photos metabox grid
    add_meta_box('aym-portfolio-info', '<strong>Projects Information</strong>', 'aym_portfolio_mbhtml', 'portfolio', 'normal', 'high');
}

function aym_portfolio_metabox_scripst(){
    $screen = get_current_screen();
    // Enqueue styles and scripts only pages
    if('post' == $screen->base && 'portfolio' == $screen->post_type){
        if(function_exists( 'wp_enqueue_media' )){
            wp_enqueue_media();
        }else{
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
        }
        
        wp_enqueue_style( 'aympmb-style', AYM_PORTFOLIO_URI . '/css/admin-metabox-style.css', array(), 1.0 );
        wp_enqueue_script( 'aympmb-scripts', AYM_PORTFOLIO_URI . '/js/admin-metaboxe-script.js', array( 'jquery', 'jquery-ui-sortable' ), RWMB_VER, true );
    }
    
    
}

/**
 * Out put the metaboxes html
 */
function aym_portfolio_mbhtml($post){
    $website = get_post_meta($post->ID, 'aymp_website', true);
    $client_name = get_post_meta($post->ID, 'aymp_client_name', true);
    $screenshots = get_post_meta($post->ID, 'aymp_screenshots', true);
    $screenshots = !empty($screenshots)? (array)$screenshots:array();
?> 
<div class="aymp-mbwrap">
    <?php wp_nonce_field('aymp-save-portfolio', 'nonce-aymp-portfolio'); ?> 
    <div class="aym-field-wrap clearfix">
        <div class="aym-lable"><label for="aymp-website"><strong>Website</strong></label></div>
        <div class="aym-field">
            <input type="text" id="aymp-website" name="aymp_website" value="<?php echo $website; ?>" placeholder="Website Url"/>
            <p class="description">Add website url to the project.</p>
        </div>
    </div>
    <div class="aym-field-wrap clearfix">
        <div class="aym-lable"><label for="aymp-clients-name"><strong>Clients Name</strong></label></div>
        <div class="aym-field">
            <input type="text" id="aymp-clients-name" name="aymp_client_name" value="<?php echo $client_name; ?>" placeholder="Clients Name"/>
            <p class="description">Add Clients name for whom the project was done.</p>
        </div>
    </div>
    <div class="aym-field-wrap clearfix">
        <div class="aym-lable"><label for="aymp-screenshots"><strong>Screenshots</strong></label></div>
        <div class="aym-field aymp-input">
            <input type="hidden" class="aymp-gallery" name="aymp_field_name" value="aymp_screenshots"/>
            <a class="aymp-mediaupload" href="#" rel="aymp_screenshots">Add Photo</a>
            <div class="aymp-scthumbs">
                <ul class="aymp-sclist aymp-shortable clearfix">
                    <?php foreach($screenshots as $atid){ 
                        $src  = wp_get_attachment_image_src( $atid, 'thumbnail' );
			$src  = $src[0];
			$link = get_edit_post_link( $atid );
                    ?>
                    <li id="aympscid-<?php echo $atid; ?>" class="aymp-single-img">
                        <img alt="" src="<?php echo $src;?>"/>
                        <div class="aymp-image-bar">
                            <a title="Edit this screenshot" class="aymp-edit-image" href="<?php echo $link; ?>" target="_blank">Edit</a> |
                            <a title="Remove this screenshot" class="aymp-remove-image" href="#" data-field_id="%s" data-attachment_id="%s">Remove</a>
                        </div>
                        <input type="hidden" name="aymp_screenshots[]" value="<?php echo $atid;?>"/>
                    </li>
                    <?php }?>
                </ul>
            </div>
            <p class="description">Add photos of the project. You can reorder the photos by dragging them.</p>
        </div>
    </div>
</div>
<?php
}


/* Save Post Meta */
add_action( 'save_post', 'aymp_save_proejct_info' );
function aymp_save_proejct_info($post_id){
// Get proper post type. @link http://www.deluxeblogtips.com/forums/viewtopic.php?id=161
    $post_type = null;
    $post = get_post( $post_id );

    if ( $post )
            $post_type = $post->post_type;
    elseif ( isset( $_POST['post_type'] ) && post_type_exists( $_POST['post_type'] ) )
            $post_type = $_POST['post_type'];

    $post_type_object = get_post_type_object( $post_type );
    // Check whether:
    // - the post is autosaved
    // - the post is a revision
    // - current post type is supported
    // - user has proper capability
    if (
            ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            || ( ! isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] )
            || ( 'portfolio' !=  $post_type )
            || ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) )
    ){
            return $post_id;
    }
    // Check Nonce for home page grid
    if (  empty( $_POST['nonce-aymp-portfolio'] ) || !wp_verify_nonce( $_POST['nonce-aymp-portfolio'], 'aymp-save-portfolio' ) ){
        return $post_id;
    }
    
    //Save the projects info
    if(!empty($_POST['aymp_website'])){
        update_post_meta($post_id, 'aymp_website', $_POST['aymp_website']);
    }else{
        delete_post_meta($post_id, 'aymp_website');
    }
    
    if(!empty($_POST['aymp_client_name'])){
        update_post_meta($post_id, 'aymp_client_name', $_POST['aymp_client_name']);
    }else{
        delete_post_meta($post_id, 'aymp_client_name');
    }
    
    if(!empty($_POST['aymp_screenshots'])){
        update_post_meta($post_id, 'aymp_screenshots', $_POST['aymp_screenshots']);
    }else{
        delete_post_meta($post_id, 'aymp_screenshots');
    }
    
}