<?php
/*
Plugin Name: HALIM Player
Plugin URI: http://halimthemes.com
Description: Ajax Player for HaLimMovie Wordpress Theme
Author: HaLim
Author URI: http://halimthemes.com
Version: 5.1
*/

define('HALIM_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_URL', plugins_url('', __FILE__));

require_once HALIM_PATH . 'includes/update-checker.php';
require_once HALIM_PATH . 'includes/youtube.class.php';
require_once HALIM_PATH . 'includes/getlink.class.php';
require_once HALIM_PATH . 'includes/playerFunctions.php';
require_once HALIM_PATH . 'includes/playerInstance.php';
require_once HALIM_PATH . 'ajaxPlayer.php';

$update_checker = Puc_v4_Factory::buildUpdateChecker(null, __FILE__, 'halimPlayer');

if(is_admin()){
    add_action('admin_init', 'halim_register_settings');
    add_action('admin_menu', 'halim_add_setting_item');
}

function halim_register_settings() {
    register_setting('halim-player-settings', 'halim_fb_token');
    register_setting('halim-player-settings', 'halim_zing_cookie');
}

function halim_add_setting_item(){
    add_options_page("Facebook Token", "Facebook Token", 'manage_options', 'halim-fb-token', 'halim_setting_page');
    add_options_page("Manage cache", "Manage cache", 'manage_options', 'halim-cache-manager', 'halim_cache_manage_page');
}

function halim_player_plugin_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=halim_options') ) .'">'.__('Settings').'</a>';
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=halim-fb-token') ) .'" style="color: #009ce6">Facebook token</a>';
   $links[] = '<a href="https://halimthemes.com" target="_blank" style="color: #3db634">More plugins by HaLimThemes</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'halim_player_plugin_action_links' );


function halim_enqueue_scripts() {
    $player_cfg = cs_get_option('halim_jw_player_options'); 
    if(is_single()) {
        if(isset($player_cfg['jw_player_library']) && $player_cfg['jw_player_library'] != ''){
            wp_enqueue_script( 'halim-jwplayer', $player_cfg['jw_player_library'], array(), '', true );
        } else {
            wp_enqueue_script( 'halim-jwplayer', plugins_url( 'assets/js/jwplayer-8.js', __FILE__ ), array(), '', true );
        }

        if(!is_singular(array('news', 'video'))) {
            wp_enqueue_script( 'halim-ajax', plugins_url( 'assets/js/halimPlayer.js', __FILE__ ), array(), '', true );
            wp_localize_script('halim-ajax', 'ajax_player', array(
                'url'   => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('halim-player-nonce'),
            ));   
        }       
    }
}
add_action('wp_enqueue_scripts', 'halim_enqueue_scripts');

function halim_setting_page(){ ?>
    <div class="wrap halim-wrap">
        <h1>Facebook Access Token</h1>
        <form method="post" action="options.php">
            <input type="hidden" name="option_page" value="halim_setting_page"/>
            <input type="hidden" name="action" value="update" />
            <?php
                settings_fields('halim-player-settings');
                $fbtoken      = get_option('halim_fb_token');
                $zing_cookie      = get_option('halim_zing_cookie');
            ?>
                <table class="form-table">   
                    <tr>
                        <th scope="row"><label>Facebook Access Token</label></th>                       
                        <td>
                            <p>Enter each token a new line</p>
                            <textarea name="halim_fb_token" rows="15" placeholder="Facebook Access Token, entering access_token with multiple lines" class="regular-text" style="width: 100%"><?php echo $fbtoken; ?></textarea>
                        </td>                                   
                    </tr>                                                 
                </table>                
                <table class="form-table">   
                    <tr>
                        <th scope="row"><label>Tv.Zing.Vn Cookie</label></th>                       
                        <td>
                            <textarea name="halim_zing_cookie" rows="15" placeholder="Tv.Zing.Vn Cookie" class="regular-text" style="width: 100%"><?php echo $zing_cookie; ?></textarea>
                        </td>                                   
                    </tr>                                                 
                </table>                
            <?php submit_button(); ?>
        </form>

    </div>
    <?php
}

function halim_cache_manage_page(){ ?>
    <div class="wrap halim-wrap">
        <h1>Cache Manager</h1>
        <div class="halim-cache-box">
            <?php 
                $cache_folder = HALIM_PATH . 'film_cache';
                $cache = new Cache($cache_folder);  
                $cache_count = json_decode($cache->cacheCount()); 
                if($cache_count->result == 1) 
                    echo '<span class="cache-count" style="color: red;">Total cache: '.$cache_count->total_cache.'</span>';

                $get_cache = json_decode($cache->getCache());
                echo '<ul class="list-cache" style="
                    max-height: 300px;
                    overflow-x: hidden;
                    border: 1px solid;
                    padding: 15px;
                ">';
                if($get_cache) {

                    foreach ($get_cache as $key => $value) {
                        echo  '<li>'.$value->file.'</li>';
                    }
                } 
                else 
                {
                    echo '<li>Cache empty!</li>';
                }
                echo '</ul>';
                ?>
            <div id="delete-all-cache" class="button button-primary">Delete all cache</div>
            <div id="result"></div>
            <script>
                jQuery(document).ready(function($){
                    jQuery('#delete-all-cache').click(function($){        
                        var confirmation = confirm("Are you sure you want to remove all cache?");    
                        if (confirmation) {                        
                            jQuery.ajax({
                                type: 'POST',
                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                data: {
                                    action: 'delete_all_cache'
                                },
                                success: function(data){
                                    jQuery('#result').html(data);
                                    jQuery('.list-cache').html('<li>Cache empty!</li>');
                                    jQuery('.cache-count').html('Total cache: 0');
                                }
                            });
                        }     
                    })
                })

            </script>
        </div>
    </div>
    <?php
}



function delete_all_cache() 
{
    $cache_folder = HALIM_PATH . 'film_cache';
    $cache = new Cache($cache_folder);
    $result = json_decode($cache->delAllCache(0), true);
    ?>
        <ul class="delete-cache">
            <li><span>Status: </span><?php echo $result['status'] == 1 ? 'Successfuly' : 'Error!'; ?></li>
            <li><span>Total cache: </span><?php echo $result['total_cache']; ?></li>
            <li><span>Cache time: </span><?php echo $result['time_limit']; ?></li>
            <li><span>Cache deleted: </span><?php echo $result['cache_deleted']; ?></li>
        </ul>

    <?php
    wp_die();
}
add_action('wp_ajax_delete_all_cache', 'delete_all_cache');
add_action('wp_ajax_nopriv_delete_all_cache', 'delete_all_cache');