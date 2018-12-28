<?php
/**
 * @package Etsy-Shop
 */
/*
Plugin Name: Etsy Shop
Plugin URI: http://wordpress.org/extend/plugins/etsy-shop/
Description: Inserts Etsy products in page or post using bracket/shortcode method.
Author: Frédéric Sheedy
Text Domain: etsy-shop
Domain Path: /languages
Version: 2.0
*/

/*
 * Copyright 2011-2018  Frédéric Sheedy  (email : sheedf@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* Roadmap
 * TODO: allow more than 100 items
 * TODO: customize currency
 * TODO: get Etsy translations
 * TODO: Use Transients API
 * TODO: Add MCE Button
 */

define( 'ETSY_SHOP_VERSION',  '2.0');

// load translation
add_action( 'init', 'etsy_shop_load_translation_file' );

// plugin activation
register_activation_hook( __FILE__, 'etsy_shop_activate' );

// check for update
add_action( 'plugins_loaded', 'etsy_shop_update' );

// add Settings link
add_filter( 'plugin_action_links', 'etsy_shop_plugin_action_links', 10, 2 );


function etsy_shop_update() {
    $etsy_shop_version = get_option( 'etsy_shop_version' );
    if ( $etsy_shop_version != ETSY_SHOP_VERSION ) {

        // upgrade logic here

        // initialize timeout option if not already there
        if( !get_option( 'etsy_shop_timeout' ) ) {
            add_option( 'etsy_shop_timeout', '10' );
        }

        // initialize cache life option if not already there
        if( !get_option( 'etsy_shop_cache_life' ) ) {
            add_option( 'etsy_shop_cache_life', '21600' ); // 6 hours in seconds
        }

        // update the version value
        update_option( 'etsy_shop_version', ETSY_SHOP_VERSION );
    }

}

function etsy_shop_load_translation_file() {
    $plugin_path = plugin_basename( dirname( plugin_basename( __FILE__ ) ) .'/translations' );
    load_plugin_textdomain( 'etsy-shop', false, $plugin_path );
}

function etsy_shop_activate() {
    etsy_shop_update();
}

/* === Used for backward-compatibility 0.x versions === */
// process the content of a page or post
add_filter( 'the_content', 'etsy_shop_post' );
add_filter( 'the_excerpt','etsy_shop_post' );

// complements of YouTube Brackets
function etsy_shop_post( $the_content ) {
    // if API Key exist
    if ( get_option( 'etsy_shop_api_key' ) ) {
        $etsy_start_tag = "[etsy-include=";
        $etsy_end_tag = "]";

        $spos = strpos( $the_content, $etsy_start_tag );
        if ( $spos !== false ) {
            $epos = strpos( $the_content, $etsy_end_tag, $spos );
            $spose = $spos + strlen( $etsy_start_tag );
            $slen = $epos - $spose;
            $tagargs = substr( $the_content, $spose, $slen );

            $args = explode( ";", $tagargs );
            if ( sizeof( $args ) > 1 ) {
                $tags = etsy_shop_process( $args[0], $args[1] );
                $new_content = substr( $the_content,0,$spos );
                $new_content .= $tags;
                $new_content .= substr( $the_content,( $epos+1 ) );
            } else {
                // must have 2 arguments
                $new_content = __( 'Etsy Shop: missing arguments', 'etsy-shop' );
            }

            // other bracket to parse?
            if ( $epos+1 < strlen( $the_content ) ) {
                $new_content = etsy_shop_post( $new_content );
            }

            return $new_content;
        } else {
            return $the_content;
        }
    } else {
        // no API Key set, return the content
        return $the_content;
    }

}
/* === END: Used for backward-compatibility 0.x versions === */

function etsy_shop_process() {

    $numargs = func_num_args();
    switch ($numargs) {
        // Used for backward-compatibility 0.x versions
        case (2):
            $shop_id            = func_get_arg(0);
            $section_id         = func_get_arg(1);
            $listing_id         = null;
            $show_available_tag = true;
            $language           = null;
            $columns            = 3;
            $thumb_size         = 'medium';
            $width              = '172px';
            $height             = '135px';
            break;
        case (1):
            $attributes         = func_get_arg(0);
            $shop_id            = $attributes['shop_name'];
            $section_id         = $attributes['section_id'];
            $listing_id         = $attributes['listing_id'];
            $show_available_tag = ( !$attributes['show_available_tag'] ? false : $attributes['show_available_tag'] );
            $language           = ( !$attributes['language'] ? null : $attributes['language']);
            $columns            = ( !$attributes['columns'] ? 3 : $attributes['columns'] );
            $thumb_size         = ( !$attributes['thumb_size'] ? "medium" : $attributes['thumb_size'] );
            $width              = ( !$attributes['width'] ? "172px" : $attributes['width'] );
            $height             = ( !$attributes['height'] ? "135px" : $attributes['height'] );
            break;
        default:
            return __( 'Etsy Shop: invalid number of arguments', 'etsy-shop' );
    }

    // Filter the values
    $shop_id    = preg_replace( '/[^a-zA-Z0-9,]/', '', $shop_id );
    $section_id = preg_replace( '/[^a-zA-Z0-9,]/', '', $section_id );
    $listing_id = preg_replace( '/[^a-zA-Z0-9,]/', '', $listing_id );

    //Filter the thumb size
    switch ($thumb_size) {
        case ("small"):
            $thumb_size = "url_75x75";
            break;
        case ("medium"):
            $thumb_size = "url_170x135";
            break;
        case ("large"):
            $thumb_size = "url_570xN";
            break;
        case ("original"):
            $thumb_size = "url_fullxfull";
            break;
        default:
            $thumb_size = "url_570xN";
            break;
    }

    // Filter Language
    if ( strlen($language) != 2 ) {
        $language = null;
    }

    if ( $shop_id != '' && $section_id != '' ) {
        // generate listing for shop section
        $listings = etsy_shop_getShopSectionListings( $shop_id, $section_id, $language );
        if ( !get_option( 'etsy_shop_debug_mode' ) ) {
            if ( !is_wp_error( $listings ) ) {
               $data = '<div class="etsy-shop-listing-container">';
               $n = 0;

               //verify if we use target blank
               if ( get_option( 'etsy_shop_target_blank' ) ) {
                   $target = '_blank';
               } else {
                   $target = '_self';
               }

               foreach ( $listings->results as $result ) {

                    if (!empty($listing_id) && $result->listing_id != $listing_id) {
                        continue;
                    }
                    $listing_html = etsy_shop_generateListing(
                        $result->listing_id,
                        $result->title,
                        $result->state,
                        $result->price,
                        $result->currency_code,
                        $result->quantity,
                        $result->url,
                        $result->Images[0]->$thumb_size,
                        $target,
                        $show_available_tag,
                        $width,
                        $height
                    );
                    if ( $listing_html !== false ) {
                        $data = $data.'<div class="etsy-shop-listing">'.$listing_html.'</div>';
                    }
                }
                $data = $data.'</div>';
            } else {
                $data = $listings->get_error_message();
            }
        } else {
            print_r( '<h2>' . __( 'Etsy Shop Debug Mode', 'etsy-shop' ) . '</h2>' );
            print_r( $listings );
        }
    } else {
        // must have 2 arguments
        $data = __( 'Etsy Shop: empty arguments', 'etsy-shop' );
    }

    return $data;
}

// Process shortcode
function etsy_shop_shortcode( $atts ) {
    // if API Key exist
    if ( get_option( 'etsy_shop_api_key' ) ) {
        $attributes = shortcode_atts( array(
            'shop_name'          => null,
            'section_id'         => null,
            'listing_id'         => null,
            'thumb_size'         => null,
            'language'           => null,
            'columns'            => null,
            'show_available_tag' => true,
            'width'              => "172px",
            'height'             => "135px"
        ), $atts );

        $content = etsy_shop_process( $attributes );
        return $content;
    } else {
        // no API Key set, return the content
        return __( 'Etsy Shop: Shortcode detected but API KEY is not set.', 'etsy-shop' );
    }
}
add_shortcode( 'etsy-shop', 'etsy_shop_shortcode' );

function etsy_shop_getShopSectionListings( $etsy_shop_id, $etsy_section_id, $language ) {
    $etsy_cache_file = dirname( __FILE__ ).'/tmp/'.$etsy_shop_id.'-'.$etsy_section_id.'_cache.json';

    // if no cache file exist
    if (!file_exists( $etsy_cache_file ) or ( time() - filemtime( $etsy_cache_file ) >= get_option( 'etsy_shop_cache_life' ) ) or get_option( 'etsy_shop_debug_mode' ) ) {
        // if language set
        if ($language != null) {
            $reponse = etsy_shop_api_request( "shops/$etsy_shop_id/sections/$etsy_section_id/listings/active", '&limit=100&includes=Images&language='.$language );
        } else {
            $reponse = etsy_shop_api_request( "shops/$etsy_shop_id/sections/$etsy_section_id/listings/active", '&limit=100&includes=Images' );
        }

        if ( !is_wp_error( $reponse ) ) {
            // if request OK
            $tmp_file = $etsy_cache_file.rand().'.tmp';
            file_put_contents( $tmp_file, $reponse );
            rename( $tmp_file, $etsy_cache_file );
        } else {
            // return WP_Error
            return $reponse;
        }
    } else {
        // read cache file
        $reponse = file_get_contents( $etsy_cache_file );
    }

    if ( get_option( 'etsy_shop_debug_mode' ) ) {
        $file_content = file_get_contents( $etsy_cache_file );
        print_r( '<h3>--- ' . __( 'Etsy Cache File:', 'etsy-shop' ) . $etsy_cache_file . ' ---</h3>' );
        print_r( $file_content );
    }

    $data = json_decode( $reponse );
    return $data;
}

function etsy_shop_getShopSection( $etsy_shop_id, $etsy_section_id ) {
    $reponse = etsy_shop_api_request( "shops/$etsy_shop_id/sections/$etsy_section_id", NULL , 1 );
    if ( !is_wp_error( $reponse ) ) {
        $data = json_decode( $reponse );
    } else {
        // return WP_Error
        return $reponse;
    }

    return $data;
}

function etsy_shop_testAPIKey() {
    $reponse = etsy_shop_api_request( 'listings/active', '&limit=1&offset=0', 1 );
    if ( !is_wp_error( $reponse ) ) {
        $data = json_decode( $reponse );
    } else {
        // return WP_Error
        return $reponse;
    }

    return $data;
}

function etsy_shop_generateShopSectionList($etsy_shop_id) {
    $list = etsy_shop_getShopSectionList($etsy_shop_id);
    if ( !is_wp_error( $list ) ) {
        $data = '';
        foreach ( $list->results as $result ) {
            $data .= '<td>'.$result->title.'</td>';
            $data .= '<td>'.$result->shop_section_id.'</td>';
            $data .= '<td>'.$result->active_listing_count.'</td>';
             $data .= '<td>[etsy-shop shop_name="'.get_option( 'etsy_shop_quickstart_shop_id' ).'" section_id="'.$result->shop_section_id.'"]</td></tr>';

        }

    } else {
        $data = 'ERROR: '.$list->get_error_message();
    }

    return $data;
}

function etsy_shop_getShopSectionList($etsy_shop_id) {
    $reponse = etsy_shop_api_request( "/shops/$etsy_shop_id/sections", NULL, 1 );
    if ( !is_wp_error( $reponse ) ) {
        $data = json_decode( $reponse );
    } else {
        // return WP_Error
        return $reponse;
    }

    return $data;
}

function etsy_shop_api_request( $etsy_request, $args = NULL, $noDebug = NULL ) {
    $etsy_api_key = get_option( 'etsy_shop_api_key' );
    $url = "https://openapi.etsy.com/v2/$etsy_request?api_key=" . $etsy_api_key . $args;
    $wp_request_args = array( 'timeout' => get_option( 'etsy_shop_timeout' ) );

    $request = wp_remote_request( $url , $wp_request_args );

    if ( get_option( 'etsy_shop_debug_mode' ) AND !$noDebug ) {
        echo( '<h3>--- ' . __( 'Etsy Debug Mode - version', 'etsy-shop' ) .' ' . ETSY_SHOP_VERSION . ' ---</h3>' );
        echo( '<p>' . __( 'Go to Etsy Shop Options page if you wan\'t to disable debug output.', 'etsy-shop' ) . '</p>' );
        print_r( '<h3>--- Etsy Request URL ---</h3>' );
        print_r( $url );
        print_r( '<h3>--- Etsy Response ---</h3>' );
        print_r( $request );
    }

    if ( !is_wp_error( $request ) ) {
        if ( $request['response']['code'] == 200 ) {
            $request_body = $request['body'];
        } else {
            if ( $request['headers']['x-error-detail'] ==  'Not all requested shop sections exist.' ) {
                return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: Your section ID is invalid.', 'etsy-shop' ) );
            } elseif ( $request['response']['code'] == 0 )  {
                return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: The plugin timed out waiting for etsy.com reponse. Please change Time out value in the Etsy Shop Options page.', 'etsy-shop' ) );
            } else {
                return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: API reponse should be HTTP 200 <br>API Error Description:', 'etsy-shop' ) . ' ' . $request['headers']['x-error-detail'] );
            }
        }
    } else {
        return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: Error on API Request', 'etsy-shop' ) );
    }

    return $request_body;
}

function etsy_shop_generateListing($listing_id, $title, $state, $price, $currency_code, $quantity, $url, $imgurl, $target, $show_available_tag, $width = "172px", $height = "135px") {
    if ( strlen( $title ) > 18 ) {
        $title = substr( $title, 0, 25 );
        $title .= "...";
    }

    // if the Shop Item is active
    if ( $state == 'active' ) {

        if ( $show_available_tag ) {
            $state = __( 'Available', 'etsy-shop' );
        } else {
            $state = '&nbsp;';
        }

        // Determine Currency Symbol
        if ( $currency_code == 'EUR' ) {
            // Euro sign
            $currency_symbol = '&#8364;';
        } else if ( $currency_code == 'GBP' ) {
            // Pound sign
            $currency_symbol = '&#163;';
        } else {
            // Dollar Sign
            $currency_symbol = '&#36;';
        }

        $script_tags =  '
            <div class="etsy-shop-listing-card" id="' . $listing_id . '" style="width:' . $width . '">
                <a title="' . $title . '" href="' . $url . '" target="' . $target . '" class="etsy-shop-listing-thumb">
                    <img alt="' . $title . '" src="' . $imgurl . '" style="height:' . $height . ';">

                <div class="etsy-shop-listing-detail">
                    <p class="etsy-shop-listing-title">'.$title.'</p>
                    <p class="etsy-shop-listing-maker">'.$state.'</p>
                </div>
                <p class="etsy-shop-listing-price">'.$currency_symbol.$price.' <span class="etsy-shop-currency-code">'.$currency_code.'</span></p>
                </a>
            </div>';

        return $script_tags;
    } else {
        return false;
    }
}

// Custom CSS

add_action( 'wp_print_styles', 'etsy_shop_css' );

function etsy_shop_css() {
    $link = plugins_url( 'etsy-shop.css', __FILE__ );
    wp_register_style( 'etsy_shop_style', $link, null, ETSY_SHOP_VERSION );
    wp_enqueue_style( 'etsy_shop_style' );
}


// Options Menu
add_action( 'admin_menu', 'etsy_shop_menu' );

function etsy_shop_menu() {
    add_options_page( __( 'Etsy Shop Options', 'etsy-shop' ), __( 'Etsy Shop', 'etsy-shop' ), 'manage_options', basename( __FILE__ ), 'etsy_shop_options_page' );
}

function etsy_shop_options_page() {
    // did the user is allowed?
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'etsy-shop' ) );
    }

    if ( isset( $_POST['submit'] ) ) {
        // did the user enter an API Key?
        if ( isset( $_POST['etsy_shop_api_key'] ) ) {
            $etsy_shop_api_key = wp_filter_nohtml_kses( preg_replace( '/[^A-Za-z0-9]/', '', $_POST['etsy_shop_api_key'] ) );
            update_option( 'etsy_shop_api_key', $etsy_shop_api_key );

            // and remember to note the update to user
            $updated = true;
        }

        // did the user enter Debug mode?
        if ( isset( $_POST['etsy_shop_debug_mode'] ) ) {
            $etsy_shop_debug_mode = wp_filter_nohtml_kses( $_POST['etsy_shop_debug_mode'] );
            //die($etsy_shop_debug_mode);
            update_option( 'etsy_shop_debug_mode', $etsy_shop_debug_mode );

            // and remember to note the update to user
            $updated = true;
        }else {
            $etsy_shop_debug_mode = 0;
            //die($etsy_shop_debug_mode);
            update_option( 'etsy_shop_debug_mode', $etsy_shop_debug_mode );

            // and remember to note the update to user
            $updated = true;
        }

        // did the user enter target new window for links?
        if ( isset( $_POST['etsy_shop_target_blank'] ) ) {
            $etsy_shop_target_blank = wp_filter_nohtml_kses( $_POST['etsy_shop_target_blank'] );
            //die($etsy_shop_debug_mode);
            update_option( 'etsy_shop_target_blank', $etsy_shop_target_blank );

            // and remember to note the update to user
            $updated = true;
        }else {
            $etsy_shop_target_blank = 0;
            //die($etsy_shop_debug_mode);
            update_option( 'etsy_shop_target_blank', $etsy_shop_target_blank );

            // and remember to note the update to user
            $updated = true;
        }

        // did the user enter an Timeout?
        if ( isset( $_POST['etsy_shop_timeout'] ) ) {
            $etsy_shop_timeout = wp_filter_nohtml_kses( preg_replace( '/[^0-9]/', '', $_POST['etsy_shop_timeout'] ) );
            update_option( 'etsy_shop_timeout', $etsy_shop_timeout );

            // and remember to note the update to user
            $updated = true;
        }

        // did the user enter an Cache life?
        if ( isset( $_POST['etsy_shop_cache_life'] ) ) {
            $etsy_shop_cache_life = wp_filter_nohtml_kses( preg_replace( '/[^0-9]/', '', $_POST['etsy_shop_cache_life'] ) );
            update_option( 'etsy_shop_cache_life', $etsy_shop_cache_life * 3600 );  // update time in hours * seconds

            // and remember to note the update to user
            $updated = true;
        }
    }

    $etsy_shop_quickstart_shop_id = null;
    $quick_start_update = false;
    if ( isset( $_POST['submitQuickstart'] ) ) {
        // did the user enter an shop name?
        if ( isset( $_POST['etsy_shop_quickstart_shop_id'] ) ) {
            $etsy_shop_quickstart_shop_id = wp_filter_nohtml_kses(  preg_replace( '/[^a-zA-Z0-9,]/', '', $_POST['etsy_shop_quickstart_shop_id'] ) );
            update_option( 'etsy_shop_quickstart_shop_id', $etsy_shop_quickstart_shop_id );
            $quick_start_update = true;
        }
    }

    // delete cache file
    if ( isset( $_GET['delete'] ) ) {

        // did a file was choosed?
        if ( isset( $_GET['file'] ) ) {
            $tmp_directory = plugin_dir_path( __FILE__ ) . 'tmp/';

            // REGEX for security!
            $filename = str_replace( '.json', '', $_GET['file'] );
            $filename = preg_replace( '/[^a-zA-Z0-9-_]/', '', $filename );

            $fullpath_filename = $tmp_directory . $filename . '.json';
            if ( file_exists( $fullpath_filename ) ) {
                $deletion = unlink( $fullpath_filename );
            } else {
                $deletion = false;
            }

            if ( $deletion ) {
                // and remember to note deletion to user
                $deleted = true;
                $deleted_file = $fullpath_filename;
            }
        }
    }

    // grab the Etsy API key
    if( get_option( 'etsy_shop_api_key' ) ) {
        $etsy_shop_api_key = get_option( 'etsy_shop_api_key' );
    } else {
        add_option( 'etsy_shop_api_key', '' );
    }

    // grab the Etsy Debug Mode
    if( get_option( 'etsy_shop_debug_mode' ) ) {
        $etsy_shop_debug_mode = get_option( 'etsy_shop_debug_mode' );
    } else {
        add_option( 'etsy_shop_debug_mode', '0' );
    }

    // grab the Etsy Target for links
    if( get_option( 'etsy_shop_target_blank' ) ) {
        $etsy_shop_target_blank = get_option( 'etsy_shop_target_blank' );
    } else {
        add_option( 'etsy_shop_target_blank', '0' );
    }

    // grab the Etsy Timeout
    if( get_option( 'etsy_shop_timeout' ) ) {
        $etsy_shop_timeout = get_option( 'etsy_shop_timeout' );
    } else {
        add_option( 'etsy_shop_timeout', '10' );
    }

    // grab the Etsy Cache life
    if( get_option( 'etsy_shop_cache_life' ) ) {
        $etsy_shop_cache_life = get_option( 'etsy_shop_cache_life' );
    } else {
        add_option( 'etsy_shop_cache_life', '21600' );
    }

    // create the Quickstart shop name
    if( !get_option( 'etsy_shop_quickstart_shop_id' ) ) {
        add_option( 'etsy_shop_quickstart_shop_id', '' );
    }

    if ( $updated ) {
        echo '<div class="updated fade"><p><strong>'. __( 'Options saved.', 'etsy-shop' ) .'</strong></p></div>';
    }

    if ( $deleted ) {
        echo '<div class="updated fade"><p><strong>'. __( 'Cache file deleted:', 'etsy-shop' ) . ' ' . $deleted_file . '</strong></p></div>';
    }

    // print the Options Page
    ?>
    <style>
        .etsty-shop-quickstart-step {
            display: inline-block;
            margin: 5px 15px 5px 5px;
            padding: 5px 10px 5px 10px;
            background-color: black;
            font-weight: bold;
            color: white;
        }
        #quickStartButton {
            display: block;
            text-decoration: none;
            margin: 10px 10px 10px 10px;
        }
        #etsy-shop-quick-start-content {
            width: 100%;
            background-color: lightgray;
            margin-top: 20px;
        }
        #etsy-shop-quickstart-sections{
            padding: 5px 20px 10px 80px;
        }
        #etsy_shop_quickstart_shop_id {
        }
    </style>
    <script>
        function quickStartButton() {
            var x = document.getElementById("etsy-shop-quick-start-content");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
    </script>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div><h2><?php _e( 'Etsy Shop Options', 'etsy-shop' ); ?></h2>
        <div id="etsy-shop-quick-start" style="margin:10px 0px 10px 0px;padding:0px;border:2px solid #dddddd;">
            <a id="quickStartButton" href="#" onclick="quickStartButton()">
                <span class="dashicons dashicons-hammer" style="color:green;"></span>
                <span style="color:green;"><?php _e( 'Click here to start easilly!', 'etsy-shop' ); ?></span>
                <span class="dashicons dashicons-arrow-down-alt2" style="color:green;"></span>
            </a>
            <div id="etsy-shop-quick-start-content" <?php if (!$quick_start_update) { ?>style="display:none;"<?php } ?>>
                    <form name="etsy_shop_quickstart_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <div class="etsty-shop-quickstart-step"><?php _e( 'STEP 1', 'etsy-shop' ); ?></div><span style="font-weight: bold;"><?php _e( 'Is your Etsy API Key is valid?', 'etsy-shop' ); ?></span>
                    <?php if ( !is_wp_error( etsy_shop_testAPIKey()) ) { $etsy_shop_qucikstart_step = 2; ?>
                        <span id="etsy_shop_api_key_status" style="color:green;font-weight:bold;"><?php _e( 'OK, go to step 2', 'etsy-shop' ); ?></span>
                    <?php } elseif ( get_option('etsy_shop_api_key') ) { ?>
                        <span id="etsy_shop_api_key_status" style="color:red;font-weight:bold;"><?php _e( 'Please configure an API Key below in this page', 'etsy-shop' ); ?></span>
                    <?php } ?>
                <?php if ( $etsy_shop_qucikstart_step === 2 ) { ?>
                    <br><div class="etsty-shop-quickstart-step"><?php _e( 'STEP 2', 'etsy-shop' ); ?></div><span style="margin-top: 8px;font-weight: bold;"><?php _e( 'What is your Shop name on etsy?', 'etsy-shop' ); ?></span>
                        <input id="etsy_shop_quickstart_shop_id" name="etsy_shop_quickstart_shop_id" type="text" size="25" value="<?php echo get_option( 'etsy_shop_quickstart_shop_id' ); ?>" class="regular-text code" />
                        <input type="submit" name="submitQuickstart" id="submitQuickstart" class="button-primary" value="<?php _e( 'Search', 'etsy-shop' ); ?>" />
                    </form>
                <?php } ?>
                <?php if ( $etsy_shop_qucikstart_step === 2 && get_option( 'etsy_shop_quickstart_shop_id' ) ) { $etsy_shop_quickstart_sections_list = etsy_shop_generateShopSectionList( get_option( 'etsy_shop_quickstart_shop_id' )); ?>
                    <br><div class="etsty-shop-quickstart-step"><?php _e( 'STEP 3', 'etsy-shop' ); ?></div><span style="margin-top: 8px;font-weight: bold;"><?php _e( 'List of sections that you can use, put the short code in your page or post:', 'etsy-shop' ); ?></span>
                    <?php if ( substr( $etsy_shop_quickstart_sections_list, 0, 3 ) === "ERR" ) { ?>
                    <p style="color:red;font-weight:bold;padding-left:80px;"><?php echo $etsy_shop_quickstart_sections_list; ?></p>
                    <?php } else { ?>
                    <table id="etsy-shop-quickstart-sections" class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th><?php _e('Shop Section Name', 'etsy-shop'); ?></th>
                                <th><?php _e('ID', 'etsy-shop'); ?></th>
                                <th><?php _e('Active listing', 'etsy-shop'); ?></th>
                                <th><?php _e('Short code', 'etsy-shop'); ?></th>
                            </tr>
                        </thead>
                        <?php echo $etsy_shop_quickstart_sections_list; ?>
                    </table>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <form name="etsy_shop_options_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="etsy_shop_api_key"></label><?php _e('Etsy API Key', 'etsy-shop'); ?>
                    </th>
                    <td>
                        <input id="etsy_shop_api_key" name="etsy_shop_api_key" type="text" size="25" value="<?php echo get_option( 'etsy_shop_api_key' ); ?>" class="regular-text code" />
                                    <?php if ( !is_wp_error( etsy_shop_testAPIKey()) ) { ?>
                                        <span id="etsy_shop_api_key_status" style="color:green;font-weight:bold;"><?php _e( 'Your API Key is valid', 'etsy-shop' ); ?></span>
                                    <?php } elseif ( get_option('etsy_shop_api_key') ) { ?>
                                        <span id="etsy_shop_api_key_status" style="color:red;font-weight:bold;"><?php _e( 'You API Key is invalid', 'etsy-shop' ); ?></span>
                                    <?php } ?>
                                    <p class="description">
                                    <?php echo sprintf( __('You may get an Etsy API Key by <a href="%1$s">Creating a new Etsy App</a>', 'etsy-shop' ), 'http://www.etsy.com/developers/register' ); ?></p>
                    </td>
                 </tr>
                 <tr valign="top">
                    <th scope="row">
                        <label for="etsy_shop_api_key"></label><?php _e('Debug Mode', 'etsy-shop'); ?></th>
                            <td>
                                <input id="etsy_shop_debug_mode" name="etsy_shop_debug_mode" type="checkbox" value="1" <?php checked( '1', get_option( 'etsy_shop_debug_mode' ) ); ?> />
                                    <p class="description">
                                    <?php echo __( 'Useful if you want to post a bug on the forum', 'etsy-shop' ); ?>
                                    </p>
                            </td>
                 </tr>
                 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_shop_target_blank"></label><?php _e('Link to new window', 'etsy-shop'); ?></th>
                             <td>
                                <input id="etsy_shop_target_blank" name="etsy_shop_target_blank" type="checkbox" value="1" <?php checked( '1', get_option( 'etsy_shop_target_blank' ) ); ?> />
                                    <p class="description">
                                    <?php echo __( 'If you want your links to open a page in a new window', 'etsy-shop' ); ?>
                                    </p>
                             </td>
                 </tr>
                 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_shop_timeout"></label><?php _e('Timeout', 'etsy-shop'); ?></th>
                             <td>
                                 <input id="etsy_shop_timeout" name="etsy_shop_timeout" type="text" size="2" class="small-text" value="<?php echo get_option( 'etsy_shop_timeout' ); ?>" class="regular-text code" />
                                    <p class="description">
                                    <?php echo __( 'Time in seconds until a request times out. Default 10.', 'etsy-shop' ); ?>
                                    </p>
                             </td>
                 </tr>
                 <tr valign="top">
                     <th scope="row">
                         <label for="etsy_shop_cache_life"></label><?php _e('Cache life', 'etsy-shop'); ?></th>
                             <td>
                                 <input id="etsy_shop_cache_life" name="etsy_shop_cache_life" type="text" size="2" class="small-text" value="<?php echo get_option( 'etsy_shop_cache_life' ) / 3600; ?>" class="regular-text code" />
                                 <?php _e('hours', 'etsy-shop'); ?>
                                  <p class="description">
                                    <?php echo __( 'Time before the cache update the listing', 'etsy-shop' ); ?>
                                  </p>
                             </td>
                 </tr>
                 <tr valign="top">
                                <th scope="row"><?php _e('Cache Status', 'etsy-shop'); ?></th>
                                <td>
                                    <?php if (get_option('etsy_shop_api_key')) { ?>
                                    <table class="wp-list-table widefat">
                                        <thead id="EtsyShopCacheTableHead">
                                        <tr>
                                            <th><?php _e('Shop Section', 'etsy-shop'); ?></th>
                                            <th><?php _e('Filename', 'etsy-shop'); ?></th>
                                            <th><?php _e('Last update', 'etsy-shop'); ?></th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <?php
                                $files = glob( dirname( __FILE__ ).'/tmp/*.json' );
                                $time_zone = get_option('timezone_string');
                                date_default_timezone_set( $time_zone );
                                foreach ($files as $file) {
                                    // downgrade to support PHP 5.2.4
                                    //$etsy_shop_section = explode( "-", strstr(basename( $file ), '_cache.json', true ) );
                                    $etsy_shop_section = explode( "-", substr( basename( $file ), 0, strpos( basename( $file ), '_cache.json' ) ) );
                                    $etsy_shop_section_info = etsy_shop_getShopSection($etsy_shop_section[0], $etsy_shop_section[1]);
                                    if ( !is_wp_error( $etsy_shop_section_info ) ) {
                                       echo '<tr><td>' . $etsy_shop_section[0] . ' / ' . $etsy_shop_section_info->results[0]->title . '</td><td>' . basename( $file ) . '</td><td>' .  date( "Y-m-d H:i:s", filemtime( $file ) ) . '</td><td><a href="options-general.php?page=etsy-shop.php&delete&file=' . basename( $file ) . '" title="'. __('Delete cache file', 'etsy-shop') .'"><span class="dashicons dashicons-trash"></span></a></td></tr>';
                                       // echo '<tr><td>' . $etsy_shop_section[0] . ' / ' . $etsy_shop_section_info->results[0]->title . '</td><td>' . basename( $file ) . '</td><td>' .  date( "Y-m-d H:i:s", filemtime( $file ) ) . '</td><td><a href="" title="' . _e('Delete cache file', 'etsy-shop'); . '"><span class="dashicons dashicons-trash"></span></a></td></tr>';
                                    } else {
                                        echo '<tr><td>' . $etsy_shop_section[0] . ' / <span style="color:red;">Error on API Request</span>' . '</td><td>' . basename( $file ) . '</td><td>' .  date( "Y-m-d H:i:s", filemtime( $file ) ) . '</td><td></td></tr>';
                                    }
                                }
                                    ?></table><?php } else { _e('You must enter your Etsy API Key to view cache status!', 'etsy-shop'); } ?>
                                <p class="description"><?php _e( 'You may reset cache a any time by deleting files in tmp folder of the plugin.', 'etsy-shop' ); ?></p>
                                </td>
                        </tr>
        </table>

        <h3 class="title"><?php _e( 'Need help?', 'etsy-shop' ); ?></h3>
        <p><?php echo sprintf( __( 'Please open a <a href="%1$s">new topic</a> on Wordpress.org Forum. This is your only way to let me know!', 'etsy-shop' ), 'http://wordpress.org/support/plugin/etsy-shop' ); ?></p>

        <h3 class="title"><?php _e( 'Need more features?', 'etsy-shop' ); ?></h3>
        <p><?php echo sprintf( __( 'Please sponsor a feature go to <a href="%1$s">Donation Page</a>.', 'etsy-shop' ), 'http://fsheedy.wordpress.com/etsy-shop-plugin/donate/' ); ?></p>

        <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e( 'Save Changes', 'etsy-shop' ); ?>" />
        </p>

        </form>
    </div>
<?php
}

// admin warning
if ( is_admin() ) {
    etsy_shop_warning();
}

function etsy_shop_warning() {
    if ( !get_option( 'etsy_shop_api_key' ) ) {
        function etsy_shop__api_key_warning() {
            echo "<div id='etsy-shop-warning' class='updated fade'><p><strong>".__( 'Etsy Shop is almost ready.', 'etsy-shop' )."</strong> ".sprintf( __( 'You must <a href="%1$s">enter your Etsy API key</a> for it to work.', 'etsy-shop' ), 'options-general.php?page=etsy-shop.php' )."</p></div>";
        }

        add_action( 'admin_notices', 'etsy_shop__api_key_warning' );
    }
}

function etsy_shop_plugin_action_links( $links, $file ) {
    if ( $file == plugin_basename( dirname( __FILE__ ).'/etsy-shop.php' ) ) {
        $links[] = '<a href="' . admin_url( 'options-general.php?page=etsy-shop.php' ) . '">'.__( 'Settings' ).'</a>';
    }

    return $links;
}

?>
