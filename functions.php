<?php

require_once 'inc/BootstrapNavMenuWalker.php';
require_once 'inc/Comments.php';

// Bootstrap Menu Walker
add_action('after_setup_theme', 'gitsta_bootstrap_setup');

// TGM Plugin Activation <http://tgmpluginactivation.com/>
// Thanks to @EmilUzelac
require_once 'inc/class-tgm-plugin-activation.php';
add_action('tgmpa_register', function() {
    $plugins = array(
        array(
            'name'      => 'WP-Markdown',
            'slug'      => 'wp-markdown',
            'required'  => false,
        ),
        array(
            'name'      => 'Gust',
            'slug'      => 'gust',
            'required'  => false
        )
    );

    tgmpa($plugins);
});

$goofy = array(1);


/*
|----------------------------------------------------------
| Gitsta Theme Setup
|----------------------------------------------------------
*/
add_action('after_setup_theme', function() {
    // Content width - required by WordPress
    global $content_width;
    if( ! isset($content_width)) {
        $content_width = 945;
    }

    // Theme support
    add_theme_support('automatic-feed-links');
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');

    // Title hook
    add_action('wp_title', function($title) {
        return get_bloginfo('name') . $title;
    });

    // Scripts and styles used by the theme
    add_action('wp_enqueue_scripts', function() {
        // Scripts and styles
        wp_enqueue_style('gitsta-style', get_stylesheet_uri());
        wp_enqueue_style('bootstrap', get_template_directory_uri() . '/vendor/bootstrap/css/bootstrap.css');
        wp_enqueue_style('font-awesome', get_template_directory_uri() . '/vendor/font-awesome/css/font-awesome.css');
        wp_enqueue_style('octicons', get_template_directory_uri() . '/vendor/octicons/css/octicons.css');

        wp_enqueue_script('bootstrap', get_template_directory_uri() . '/vendor/bootstrap/js/bootstrap.js', array('jquery'));
        wp_enqueue_script('marked', get_template_directory_uri() . '/vendor/marked.js');

        wp_enqueue_script('gitsta-main-js', get_template_directory_uri() . '/js/main.js');

        wp_localize_script('gitsta-main-js', 'theme', array('template_directory_uri' => get_template_directory_uri()));
    });


    /*
    |----------------------------------------------------------
    | Sidebar
    |----------------------------------------------------------
    |
    | Sidebar is hidden on mobile devices
    |
    */
    if(function_exists('register_sidebar')) {
        add_action('widgets_init', function() {
            register_sidebar(array(
                'before_widget' => '<div id="%1$s" class="widget hidden-xs hidden-sm">',
                'after_widget'  => '</div>'
            ));
        });
    }


    /*
    |----------------------------------------------------------
    | Filters
    |----------------------------------------------------------
    */
	add_filter( 'default_content', 'my_editor_content' );
	function my_editor_content( $content ) {
	/*$content = "<h3>Aim</h3><p>Why were you doing this experiment?</p><br><h3>Methods</h3><p>What did you do?</p><br><h3>Results</h3><p>What happened?</p><br><h3>Thoughts</h3><p>What does it mean?</p>";*/
	$content = "";
	return $content;
	}
    // CSS Style for reply link
    add_filter('comment_reply_link', function($link) {
        $link = str_replace("class='comment-reply-link'", "class='comment-reply-link btn btn-default btn-xs' data-scroll='true'", $link);
        return $link;
    });

    // CSS Style for edit comment link
    add_filter('edit_comment_link', function($link) {
        $link = str_replace('class="comment-edit-link"', "class='comment-edit-link btn btn-default btn-xs'", $link);
        return $link;
    });

    // CSS Style for edit post link
    add_filter('edit_post_link', function($link) {
        $link = str_replace('class="post-edit-link"', "class='post-edit-link btn btn-info btn-xs'", $link);
        return $link;
    });

    // Add some css classes to images
    add_filter('the_content', function($content) {
        $pattern        = "/<img(.*?)class=\"(?!wp-smiley)(.*?)\"(.*?)>/i";
        $replacement    = '<img$1class="$2 img-thumbnail img-responsive"$3>';
        $content        = preg_replace($pattern, $replacement, $content);
        return $content;
    });

    // Add bootstrap css classes to tables
    add_filter('the_content', function($content) {
        $pattern        = "/<table(.*?)>/i";
        $replacement    = '<table $1 class="table table-bordered table-striped">';
        $content        = preg_replace($pattern, $replacement, $content);

        return $content;
    });

    // Adds some css classes to avatars
    add_filter('get_avatar', function($avatar) {
        $avatar = str_replace("class='avatar",
                              "class='avatar media-object img-rounded",
                              $avatar);
        return $avatar;
    });

    // Add css class to tags
    add_filter('the_tags', function($tag) {
        $tag = str_replace('rel="tag">',
                           'rel="tag" class="label label-primary">',
                           $tag);

        return $tag;
    });

    // Code tags
    add_filter('the_content', function($content) {
        $pattern        = "/<pre(.*?)>/i";
        $replacement    = '<div class="code-box"><div class="code-title"><i class="fa fa-code"></i> <div class="pull-right"><a href="#" class="btn btn-default btn-xs toggle-code" data-toggle="tooltip" title="' . __('Toggle code', 'gitsta') . '"><i class="fa fa-toggle-up"></i></a></div></div><pre $1>';
        $content        = preg_replace($pattern, $replacement, $content);

        $pattern        = "/<\/pre>/";
        $replacement    = '</pre></div>';
        $content        = preg_replace($pattern, $replacement, $content);

        return $content;
    });

    // Read more Hack
    add_filter('the_content_more_link', function($link) {
        $link = preg_replace( '|#more-[0-9]+|', '', $link );
	return $link;
    });


    /*
    |----------------------------------------------------------
    | Theme options
    |----------------------------------------------------------
    */
    // To reduce redundancy
    function get_gitsta_theme_option($option) {
        // Default theme options (somehow a senseless comment)
        $default_theme_options = array(
            'favicon_url'               => '',

            'comment_markdown_support'  => 1,
            'frontpage_blog_descr'      => 0,

            'error_404_title'           => 'Error 404',
            'error_404_content'         => '<p class="text-muted">
                    Whoops, you found a dead link. Sorry about that.
                </p>',
        );

        $gitsta_theme_options = get_option('gitsta_theme_options', $default_theme_options);

        if(isset($gitsta_theme_options[$option])) {
            return $gitsta_theme_options[$option];
        }

        return false;
    }

    add_action('admin_init', function() {
        register_setting('gitsta_options', 'gitsta_theme_options', function($input) {
            return $input;
        });
    });
    add_action('admin_menu', function() {
        add_theme_page('Gitsta', 'Gitsta', 'edit_theme_options', 'gitsta-option', function() {
            include 'inc/Partials/ThemeOptions.php';
        });
    });


    /*
    |----------------------------------------------------------
    | Custom wp_link_pages function
    |----------------------------------------------------------
    */
    function gitsta_wp_link_pages($args = '') {
        $defaults = array(
            'before'           => '',
            'after'            => '',
            'prev_and_next'    => true,
            'nextpagelink'     => '&raquo;',
            'prevpagelink'     => '&laquo;',
            'echo'             => true
        );

        $r = wp_parse_args($args, $defaults);
        extract($r, EXTR_SKIP);

        global $page, $numpages, $multipage, $more;

        $output = '';
        if($multipage) {
            $output .= $before . '<ul class="pagination">';

            // Attach prev link
            $i = ($page - 1);
            if(($i > 0) && $prev_and_next) {
                $output .= '<li>' . _wp_link_page($i) . $prevpagelink . '</a></li>';
            }

            // Attach page numbers
            for($i = 1; $i <= $numpages; $i++) {
                $link = '';
                if($i == $page) {
                    $link .= '<li class="active"><a href="#">' . $i . '</li>';
                } else {
                    $link .= '<li>' . _wp_link_page($i) . $i . '</a></li>';
                }

                $link    = apply_filters('wp_link_pages_link', $link, $i);
                $output .= $link;
            }

            // Attach next link
            $i = ($page + 1);
            if(($i <= $numpages) && $prev_and_next) {
                $output .= '<li>' . _wp_link_page($i) . $nextpagelink . '</a></li>';
            }

            $output .= '</ul>' . $after;
        }

        $output = apply_filters('wp_link_pages', $output, $args);
        if($echo) {
            echo $output;
        }
    }
});


function hide_publishing_actions(){
        $my_post_type = 'post';
        global $post;
        if($post->post_type == $my_post_type){
            echo '
                <style type="text/css">
                #visibility,
		.misc-pub-post-status,
		#postimagediv {
                        display:none;
                    }
                </style>
            ';
        }
}
add_action('admin_head-post.php', 'hide_publishing_actions');
add_action('admin_head-post-new.php', 'hide_publishing_actions');


/*
Remove Howdy
*/
add_action( 'admin_bar_menu', 'wp_admin_bar_my_custom_account_menu', 11 );
function wp_admin_bar_my_custom_account_menu( $wp_admin_bar ) {
$user_id = get_current_user_id();
$current_user = wp_get_current_user();
$profile_url = get_edit_profile_url( $user_id );

if ( 0 != $user_id ) {
/* Add the "My Account" menu */
$avatar = get_avatar( $user_id, 28 );
/* $howdy = sprintf( __('Welcome, %1$s'), $current_user->display_name ); */
$howdy = sprintf( __('%1$s'), $current_user->display_name );
$class = empty( $avatar ) ? '' : 'with-avatar';

$wp_admin_bar->add_menu( array(
'id' => 'my-account',
'parent' => 'top-secondary',
'title' => $howdy . $avatar,
'href' => $profile_url,
'meta' => array(
'class' => $class,
),
) );

}
}

/*
|----------------------------------------------------------
| Remove comment editing on authors own post (can still edit own comments)
|----------------------------------------------------------
*/

// http://scribu.net/wordpress/prevent-blog-authors-from-editing-comments.html
function restrict_comment_editing( $caps, $cap, $user_id, $args ) {
	if ( 'edit_comment' == $cap ) {
		$comment = get_comment( $args[0] );
  
		if ( $comment->user_id != $user_id )
			$caps[] = 'moderate_comments';
	}
  
	return $caps;
}
add_filter( 'map_meta_cap', 'restrict_comment_editing', 10, 4 );

/*
|----------------------------------------------------------
| Use a comment date stamp not 'x days ago'
|----------------------------------------------------------
*/

// https://wpsites.net/wordpress-admin/change-comment-date-format-using-get_comment_date-filter/

//add_filter( 'get_comment_date', 'wpsites_change_comment_date_format' );	
//function wpsites_change_comment_date_format( $d ) {
//    $d = date("y-m-d");	
//    return $d;
//} 

/*
|----------------------------------------------------------
| Add custom shortcodes for inline style tweaks
|----------------------------------------------------------
*/

// https://www.problogdesign.com/wordpress/working-with-wordpress-shortcodes/

function shortcode_DM_conf() {
   return '<span style="text-align:center;color: red; font-family: courier,sans serif;margin:10px auto;padding:5px;background:#ffe187;font-size:105%;">[CONFIDENTIAL!] The contents of this post should not be shared beyond CCI staff.</span>';
}
add_shortcode('conf', 'shortcode_DM_conf');

function shortcode_DM_ijmenu( $atts, $content = null ) {
   return '<span style="font-family: courier,sans serif;color:#0046ff;">' . $content . '</span>';
}
add_shortcode('ijmenu', 'shortcode_DM_ijmenu');
