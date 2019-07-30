<?php

function halim_ajax_player()
{
    $clean = new xssClean();  
    $cache_time = cs_get_option('player_cache_time');   
    $player_cfg = cs_get_option('halim_jw_player_options');
    $post_ID = $clean->clean_input(wp_strip_all_tags($_POST['postid']));
    $episode = $clean->clean_input(wp_strip_all_tags($_POST['episode']));
    $server  = $clean->clean_input(wp_strip_all_tags($_POST['server']));  
    $meta = get_post_meta($post_ID, '_halim_metabox_options', true );
    $captions_color = isset($player_cfg['jw_tracks_color']) ? $player_cfg['jw_tracks_color'] : '';     
    $captions_font_size = isset($player_cfg['jw_tracks_font_size']) && $player_cfg['jw_tracks_font_size'] != '' ? $player_cfg['jw_tracks_font_size'] : 14;     
    $poster = isset($meta['halim_poster_url']) && $meta['halim_poster_url'] != '' ? $meta['halim_poster_url'] : '';       
    $halimmoviesMetaPost = get_post_meta( $post_ID, '_halimmovies', true );
    $data = json_decode($halimmoviesMetaPost);
    $dataPlayer = array();
    if(isset($data[$server-1]->halimmovies_server_data[$episode-1])){
        $dataPlayer = $data[$server-1]->halimmovies_server_data[$episode-1];
    }
    else if(isset($data[0]->halimmovies_server_data[0])){
        $dataPlayer = $data[0]->halimmovies_server_data[0];
    }
    if($dataPlayer && isset($dataPlayer->halimmovies_ep_link))
    {
        $halimmovies_subs = isset($dataPlayer->halimmovies_ep_subs) ? $dataPlayer->halimmovies_ep_subs : '';
        $tracks = '';
        $tracks .= 'tracks: [';
        if($halimmovies_subs)
        {
            $subs = '';
            foreach ($halimmovies_subs as $key => $value) 
            {
                $default = $key == 0 ? 'true' : 'false';
                $subs .= '{
                            file: "'.PLUGIN_URL.'/readsub.php?file='.trim($value->halimmovies_ep_sub_file).'", 
                            label: "'.trim($value->halimmovies_ep_sub_label).'", 
                            kind: "captions", 
                            default: '.$default.' 
                        },';
            }
            if($subs)
                $tracks .= substr($subs, 0, -1);
        }
        $tracks .= '],
                    captions: {
                        color: "'.$captions_color.'",
                        fontSize: '.$captions_font_size.',
                        backgroundOpacity: 0,
                        edgeStyle: "raised"
                    },';

        if($dataPlayer->halimmovies_ep_type == 'embed'){
            halim_detect_embed($dataPlayer->halimmovies_ep_link); 
        } 
        elseif($dataPlayer->halimmovies_ep_type == 'mp4') 
        {
            $result[] = array(
                'file' => $dataPlayer->halimmovies_ep_link,
                'label' => 'FULL HD',
                'type' => 'video/mp4' 
            );
            $sources = json_encode($result);                  
            halim_build_jwplayer($sources, $tracks, $poster);
        } 
        else 
        {           
            $sources = halim_detect_server($dataPlayer->halimmovies_ep_link); 
            // if(strpos($dataPlayer->halimmovies_ep_link, 'facebook.com'))
            // {
            //     halim_get_facebook_player($sources);               
            // } 
            // else 
            // {
                halim_build_jwplayer($sources, $tracks, $poster);
            // }

        }
    } 
    wp_die();
}

add_action('wp_ajax_halim_ajax_player', 'halim_ajax_player');
add_action('wp_ajax_nopriv_halim_ajax_player', 'halim_ajax_player');

function halim_ajax_get_listsv_player() 
{
	$clean = new xssClean();
    $postid = $clean->clean_input(wp_strip_all_tags($_POST['postid']));
    $server = $clean->clean_input(wp_strip_all_tags($_POST['server']));
    $episode = $clean->clean_input(wp_strip_all_tags($_POST['episode']));
    $ep_link = $clean->clean_input(wp_strip_all_tags($_POST['ep_link']));

    if(!$server) $server = 1;
    if($episode) $episode = ($episode-1);

    $metaPost = get_post_meta( $postid, '_halimmovies', true );
    $data = json_decode($metaPost);

    $dataPlayer = array();
    if(isset($data[$server-1]->halimmovies_server_data[$episode])){
        $dataPlayer = $data[$server-1]->halimmovies_server_data[$episode];
    }
    elseif(isset($data[0]->halimmovies_server_data[0])){
        $dataPlayer = $data[0]->halimmovies_server_data[0];
    }
    $player_cfg = cs_get_option('halim_jw_player_options');
    $captions_color = isset($player_cfg['jw_tracks_color']) ? $player_cfg['jw_tracks_color'] : '';
    $captions_font_size = isset($player_cfg['jw_tracks_font_size']) && $player_cfg['jw_tracks_font_size'] != '' ? $player_cfg['jw_tracks_font_size'] : 14;
    $halimmovies_subs = isset($dataPlayer->halimmovies_ep_subs) ? $dataPlayer->halimmovies_ep_subs : '';
    $tracks = $poster = '';
    $tracks .= 'tracks: [';
    if($halimmovies_subs)
    {
        $subs = '';
        foreach ($halimmovies_subs as $key => $value) 
        {
            $default = $key == 0 ? 'true' : 'false';
            $subs .= '{
                        file: "'.PLUGIN_URL.'/readsub.php?file='.trim($value->halimmovies_ep_sub_file).'", 
                        label: "'.trim($value->halimmovies_ep_sub_label).'", 
                        kind: "captions", 
                        default: '.$default.' 
                    },';
        }
        if($subs)
            $tracks .= substr($subs, 0, -1);
    }
    $tracks .= '],';    
    $tracks .= 'captions: {
                    color: "'.$captions_color.'",
                    fontSize: '.$captions_font_size.',
                    backgroundOpacity: 0,
                    edgeStyle: "raised"
                },';

    $halimmovies_listsv = isset($dataPlayer->halimmovies_ep_listsv) ? $dataPlayer->halimmovies_ep_listsv : '';

    $link = $type = array(); 
    if($halimmovies_listsv){
        foreach ($halimmovies_listsv as $key => $value) {            
            $link[$key+1] = $value->halimmovies_ep_listsv_link;
            $type[$key+1] = $value->halimmovies_ep_listsv_type;
        }
    }   
    $url = $link[$ep_link];
    $ep_type = $type[$ep_link];    
    if($ep_type == 'embed'){
        halim_detect_embed($url); 
    } elseif($ep_type == 'mp4') {
        $result[] = array(
            'file'  => $url,
            'label' => 'FULL HD',
            'type'  => 'video/mp4' 
        );
        $sources = json_encode($result);                  
        halim_build_jwplayer($sources, $tracks, $poster);        
    } else {         
        // $is_fb = (strpos($url, 'facebook.com')) ? true : false;
        $sources = halim_detect_server($url); 
        // if(strpos($dataPlayer->halimmovies_ep_link, 'facebook.com'))
        // {
        //     halim_get_facebook_player($sources);               
        // } 
        // else {
            halim_build_jwplayer($sources, $tracks, $poster);
        // }
    }

    wp_die();     
}
add_action('wp_ajax_nopriv_halim_play_listsv', 'halim_ajax_get_listsv_player');
add_action('wp_ajax_halim_play_listsv', 'halim_ajax_get_listsv_player'); 

function halim_get_facebook_player($sources)
{
    echo '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://apps-1775163339255201.apps.fbsbx.com/instant-bundle/2515356061822517/1874743292634561/index.html?url='.base64_encode($sources).'" allowfullscreen></iframe></div>';
}