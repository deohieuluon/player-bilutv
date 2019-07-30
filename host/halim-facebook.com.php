<?php 

class halim_facebook_com extends HALIM_GetLink
{ 
    public function get_link($link) 
    {
        $tokens = preg_split('/\r\n|[\r\n]/', get_option('halim_fb_token'));
        $id = $this->get_Fb_Vid($link);

        foreach($tokens as $key=> $token) 
        {
            $data = HALIMHelper::cURL('https://graph.facebook.com/fql?q=SELECT+vid,title,thumbnail_link,src,src_hq+FROM+video+WHERE+vid='.$id.'&access_token='.$token);
            $data = json_decode($data, true)['data'][0];
            if($data != NULL) 
            {
                if(!empty($data['src_hq'])) {
                    $json[] = array(
                        'file'      => $this->halim_fbcdn($data['src_hq']),
                        'type'      => 'video/mp4',
                        'label'     => '720p HD',
                        'default' => true
                    );
                }
                $json[] = array(
                    'file'      => $this->halim_fbcdn($data['src']),
                    'type'      => 'video/mp4',
                    'label'     => '360p SD'
                );
                return json_encode($json);

                break; 

            } else {
                return $this->get_sources($id, $tokens);      
            }
        }      
    }

    private function halim_fbcdn($url) {
        return $url;
        // return str_replace(array('video.xx', '_nc_ht=scontent.fvca1-1.fna'), array('scontent.fvca1-1.fna', '_nc_ht=video.xx'), $url);
    }

    private function get_sources($id, $tokens) 
    {
        foreach($tokens as $key=> $token) 
        {
            $data = HALIMHelper::cURL("https://graph.facebook.com/{$id}?access_token={$token}"); 
            $data = json_decode($data, true);
            if($data['source'] != NULL)
            {
                $result[] = array('file' => $this->halim_fbcdn($data['source']), 'label' => 'Auto', 'type' => 'video/mp4');   
                return json_encode($result);
                break;                 
            }                      
        }
    }


    private function get_Fb_Vid($url) 
    {
        if(!preg_match("~/(videos|permalink)~i", $url)) {
            preg_match('/facebook\.com\/(\d+)/is', $url, $id); 
            $result = $id[1];
        } else {           
            // preg_match('/(videos|permalink)(\/|=)(\d+)(\/|&)?/', $url, $id);
            preg_match("~(videos|permalink)/(?:t\.\d+/)?(\d+)~i", $url, $id); 
            $result = $id[2];
        }
        return $result;
    }

}


// class halim_facebook_com extends HALIM_GetLink
// {
//     public function get_link($link)
//     {   
//         $cookie = get_option('halim_fb_cookie');
//         $data = $this->cURL($link, $cookie);
//         if(preg_match('/hd_src_no_ratelimit:"(.*?)"/is', $data, $match)){
//             $hd_url = $match[1];
//             $result[] = array(
//                 'file' => $hd_url,
//                 'label' => '720p HD',
//                 'type' => 'video/mp4' 
//             );                    
//         }        

//         if(preg_match('/sd_src_no_ratelimit:"(.*?)"/is', $data, $match)){ 
//             $sd_url = $match[1];        
//             $result[] = array(
//                 'file' => $sd_url,
//                 'label' => '480p SD',
//                 'type' => 'video/mp4' 
//             );        
//         }
//         return json_encode($result);                
//     }       

//     public function get_link_full($link)
//     {   
//         $cookie = get_option('halim_fb_cookie');
//         $data = $this->cURL($link, $cookie);
//         preg_match_all("~x3CBaseURL>(.*?)x3C/BaseURL>~", $data, $links);
//         $files = $links[1];
//         $label = array(0 => '360p', 1 => '480p', 2 => '720p', 3 => '1080p', 4 => 'Audio');
//         // array_pop($files);
//         foreach($files as $k => $file) 
//         {       
//             $file = str_replace(array('&amp;', '\\'), array('&', ''), $file);
//             $res[] = array(
//                 'file' => $file,
//                 'label' => $label[$k],
//                 'type' => 'video/mp4',
//                 'default' => $label[$k] == '720p' ? 'true' : 'false'
//             );
//         }

//         return json_encode($res);               
//     }

//     private function getFbVideoID($url) 
//     {
//         if(!preg_match("~/(videos|permalink)~i", $url)) 
//         {
//             preg_match('/facebook\.com\/(\d+)/is', $url, $id); 
//             $result = $id[1];
//         } 
//         else 
//         {           
//             preg_match("~(videos|permalink)/(?:t\.\d+/)?(\d+)~i", $url, $id); // preg_match('/(videos|permalink)(\/|=)(\d+)(\/|&)?/', $url, $id);
//             $result = $id[2];
//         }
//         return $result;
//     }

//     private function cURL($url, $cookie='') 
//     {
//         $ch = @curl_init();
//         $head[] = "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
//         $head[] = "accept-encoding: gzip, deflate";
//         $head[] = "accept-language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5";
//         $head[] = "cache-control: max-age=0";
//         $head[] = "cookie: ".$cookie."";
//         curl_setopt($ch, CURLOPT_URL, $url);
//         curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36');
//         curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
//         curl_setopt($ch, CURLOPT_ENCODING , 'gzip, deflate');
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//         curl_setopt($ch, CURLOPT_REFERER, "https://www.facebook.com");
//         curl_setopt($ch, CURLOPT_TIMEOUT, 60);
//         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
//         $response = curl_exec($ch);
//         curl_close($ch);
//         return $response;
//     }

// }
