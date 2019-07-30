<?php 

function halim_detect_server($link)
{
    $enable_cache = cs_get_option('player_cache');
    $cache_time = cs_get_option('player_cache_time');
    $folderCache = HALIM_PATH . 'film_cache';
    $cache = new Cache($folderCache, $cache_time);
    $getlink = new HALIM_GetLink; 
    $getlink->init($link);
    if($enable_cache == true){
        $cacheData = $cache->readCache($link);
        if($cacheData){
            $streams = json_decode($cacheData);
        } else {              
            $streams = $getlink->host->get_link($getlink->_url);             
            $cache->saveCache($link, json_encode($streams));
        }           
    } else {
        $streams = $getlink->host->get_link($getlink->_url);
    }
    return $streams; 
}

function halim_detect_embed($url)
{
    $embed_url = '';
    if(strpos($url, 'youtube')) {           
        $id = HALIMHelper::getYoutubeId($url);
        $embed_url = '//www.youtube.com/embed/'.$id;
    } elseif(strpos($url, 'drive')) {            
        $embed_url = str_replace('view', 'preview', $url);
    } elseif (strpos($url, 'dailymotion')) {
        $id = HALIMHelper::getDailyMotionId($url);
        $embed_url = '//www.dailymotion.com/embed/video/'.$id;
    } elseif(strpos($url, 'vimeo')) {   
        $id = HALIMHelper::getVimeoId($url);      
        $embed_url = '//player.vimeo.com/video/'.$id;
    } elseif (strpos($url, 'openload')) {
       preg_match('@https?://\w+\.(.*?)/(.*?)/([\w-]+)@i', $url, $id);
       $embed_url = '//openload.co/embed/'.$id[3].'/';
    } else {
        $embed_url = $url;               
    }
    echo '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="'.$embed_url.'" allowfullscreen></iframe></div>';
}

function halim_build_jwplayer($sources, $tracks, $poster)
{   
    global $post;
    $clean                  = new xssClean();
    $player_ad_cfg          = cs_get_option('halim_jw_player_ads'); 
    $player_cfg             = cs_get_option('halim_jw_player_options');
    $player_skin            = isset($player_cfg['jw_player_skin']) ? $player_cfg['jw_player_skin'] : '';
    $player_logo            = isset($player_cfg['jw_player_logo']) ? $player_cfg['jw_player_logo'] : '';
    $player_logo_hide       = isset($player_cfg['jw_player_logo_hide']) && $player_cfg['jw_player_logo_hide'] == 1 ? "true" : "false";
    $logo_position          = isset($player_cfg['jw_player_logo_position']) ? $player_cfg['jw_player_logo_position'] : '';
    $player_logo_link       = isset($player_cfg['jw_player_logo_link']) ? $player_cfg['jw_player_logo_link'] : 'https://halimthemes.com';
    $player_ver             = isset($player_cfg['jw_player_version']) ? $player_cfg['jw_player_version'] : '';
    $player_lib             = isset($player_cfg['jw_player_library']) ? $player_cfg['jw_player_library'] : '';
    $jwplayer_key           = isset($player_cfg['jw_player_license_key']) && $player_cfg['jw_player_license_key'] != '' ? $player_cfg['jw_player_license_key'] : 'MBvrieqNdmVL4jV0x6LPJ0wKB/Nbz2Qq/lqm3g==';
    $player_poster          = isset($player_cfg['jw_player_poster_image']) && $player_cfg['jw_player_poster_image'] != '' ? $player_cfg['jw_player_poster_image'] : $poster;
    $player_autoplay        = isset($player_cfg['jw_player_autoplay']) && $player_cfg['jw_player_autoplay'] == true ? 'true' : 'false';
    $player_download        = isset($player_cfg['jw_player_download']) ? $player_cfg['jw_player_download'] : false;
    $player_autonext        = isset($player_cfg['jw_player_autonext']) ? $player_cfg['jw_player_autonext'] : false;
    $player_sharing         = isset($player_cfg['jw_player_sharing_block']) ? $player_cfg['jw_player_sharing_block'] : '';
    $player_share           = isset($player_cfg['jw_player_share']) ? $player_cfg['jw_player_share'] : '';
    $player_about_text      = isset($player_cfg['jw_player_about_text']) ? $player_cfg['jw_player_about_text'] : '';
    $player_about_link      = isset($player_cfg['jw_player_about_link']) ? $player_cfg['jw_player_about_link'] : '';
    $jw_player_show_ad      = isset($player_ad_cfg['jw_player_show_ad']) ? $player_ad_cfg['jw_player_show_ad'] : '';
    $player_ad_client       = isset($player_ad_cfg['jw_player_ads_client']) ? $player_ad_cfg['jw_player_ads_client'] : '';
    $player_ad_vpaidmode    = isset($player_ad_cfg['jw_player_ads_vpaidmode']) ? $player_ad_cfg['jw_player_ads_vpaidmode'] : '';
    $jw_player_preload_ad   = isset($player_ad_cfg['jw_player_preload_ads']) ? $player_ad_cfg['jw_player_preload_ads'] : '';
    $player_ad_tag          = isset($player_ad_cfg['jw_player_ads_tag']) ? $player_ad_cfg['jw_player_ads_tag'] : '';
    $player_ad_skip         = isset($player_ad_cfg['jw_player_ads_skip']) ? $player_ad_cfg['jw_player_ads_skip'] : '';
    $player_ad_skip_msg     = isset($player_ad_cfg['jw_player_ads_skip_msg']) ? $player_ad_cfg['jw_player_ads_skip_msg'] : '';
    $player_ad_skip_text    = isset($player_ad_cfg['jw_player_ads_skip_text']) && $player_ad_cfg['jw_player_ads_skip_text'] ? $player_ad_cfg['jw_player_ads_skip_text'] : 'Skip this ad in XX';
    $logoPlayer = $sharing_box = $download = $autonext = $result = $skinPlayer = $ad_code = '';
    if($player_logo != '') 
    {
        $logoPlayer .= 'logo: {
                    file: "'.$player_logo.'",
                    link: "'.$player_logo_link.'",
                    hide: '.$player_logo_hide.',
                    logoBar: "'.$player_logo.'",
                    target: "_blank", 
                    position: "'.$logo_position.'",
                },';
    }

    if($player_skin == 'halim') {
        $skinPlayer .= 'skin: {
                    name: "halimthemes",
                    url: "'.PLUGIN_URL.'/assets/css/halim.css?v1.0",
                },';
    } else {
        $skinPlayer .= 'skin: {"name": "'.$player_skin.'"},';
    }   
    if($player_sharing == true){
        $sharing_box .= 'sharing: {
                sites: ["reddit","facebook","twitter","googleplus","email","linkedin"], 
                heading: "'.$player_share.'"
            },';
    }

    if($player_download == true) {
        $download .= 'playerInstance.addButton(
                        "'.PLUGIN_URL.'/assets/img/downloads.png",
                        "Download Video",
                        function() {
                            window.open(jwplayer("halim-player").getPlaylistItem()["file"] + "?type=video/mp4&title="+document.title+"", "download").blur();
                            // window.location.href = jwplayer().getPlaylistItem()[\'file\'] + "?type=video/mp4&title="+document.title+"";
                        }, "download"
                    );';
    }    
    if($jw_player_show_ad == 1) {     
        $ad_code = 'advertising: {
            tag: "'.$player_ad_tag.'",
            client: "'.$player_ad_client.'",
            vpaidmode: "'.$player_ad_vpaidmode.'",
            preloadAds: "'.$jw_player_preload_ad.'",
            skipoffset: "'.$player_ad_skip.'",
            skipmessage: "'.$player_ad_skip_msg.'",
            skiptext: "'.$player_ad_skip_text.'"        
        },';
    }



    $eps = $clean->clean_input(sanitize_text_field($_POST['episode'])); 
    $sv = $clean->clean_input(sanitize_text_field($_POST['server']));
    $post_id = $clean->clean_input(absint($_POST['postid']));
    $arrSV = HaLimCore::halim_get_list_server($post_id, $eps, $sv);

    $check = json_decode($sources);
    $sources = ($sources == '[]' || $sources == '' || $sources == 'null' || empty($check[0]->file)) ? '[{ file: "//content.jwplatform.com/videos/not-a-real-video-file.mp4", label: "720p", type: "video/mp4"}]' : $sources; 

    playerInstance($jwplayer_key, $sources, $player_poster, $sharing_box, $player_autoplay, $skinPlayer, $logoPlayer, $tracks, $ad_code); 

    ?>

    <script>
        <?php echo $download.$autonext; ?>        
        if (!isMobile.any()) {
            playerInstance.on('ready', function() {
                halim_add_btn.addResizeBar(playerInstance);
                halim_add_btn.addToggleLight(playerInstance);
            });    
        }      
        playerInstance.on('error', function() {
            detectServer(); 
        });        
        <?php if($player_autonext == true) : ?>
            playerInstance.on('complete', function(){
                if(jQuery("#autonext-status").text() == "On")
                {
                    var position = jQuery(".halim-list-eps").find(".active").data("position"); 
                    var eps_title = jQuery(".halim-info-<?php echo $sv; ?>-<?php echo ($eps+1); ?>").data('title');
                    if(position != "last")
                    {
                        halim_Player(<?php echo ($eps+1); ?>, <?php echo $sv; ?>, halim_cfg.post_id, '');
                        if(history.pushState) {
                            var slug = (halim_cfg.type_slug == 'slug-1') ? halim_cfg.server_slug+'-<?php echo $sv; ?>' : halim_cfg.server_slug+'<?php echo $sv; ?>';
                            history.pushState("", "", halim_cfg.post_url +'-'+halim_cfg.eps_slug+'-<?php echo ($eps+1); ?>-'+slug+'/');               
                            document.title = eps_title;
                            $('h1.entry-title').html(eps_title);
                        }
                        jQuery("#halim-player-loader").show().html('<p style="margin-top: 40%;">Next Episode. Please wait...</p>');
                        jQuery(".halim-btn").removeClass("active");
                        jQuery(".halim-info-<?php echo $sv; ?>-<?php echo ($eps+1); ?>").addClass("active");
                    }
                }
            });

        <?php endif; ?>   
        function detectServer()
        {
            HaLim.ReportError(<?php echo $sv; ?>, <?php echo $eps; ?>);
            if($('#halim-ajax-list-server .no-active').length) {
                halim_Player(<?php echo $eps; ?>, <?php echo $sv; ?>, <?php echo $post_id; ?>, <?php echo $arrSV; ?>);
                jQuery("#halim-player-loader").show().html('<p style="margin-top: 35%;padding:15px;">Rebuilding player, please wait...</p>');
                jQuery("#get-eps-<?php echo $arrSV; ?>").addClass('checked');
                jQuery("#get-eps-<?php echo $arrSV; ?>").removeClass('no-active').addClass('active').siblings().removeClass('active');           
            }
            else {
                jQuery("#halim-player-loader").show().html('<p style="margin-top: 33%;"><h2>Sorry!</h2> We are unable to find the video you are looking for. There could be several reasons for this, for example it got removed by the owner!</p>');
            }
        }
    </script>
    <?php
    die();
}