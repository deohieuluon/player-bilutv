<?php 

	class halim_photos_google_com extends HALIM_GetLink
	{
		function get_link($link)
		{
            if(isset($link)){
            	$curTemp = HALIMHelper::cURL($link);
            	$curTemp = $this->cut_str($curTemp,'{"79468658":[[','"]');
            	$curTemp = str_replace('\u003d','=', $curTemp);
            	$curTemp = str_replace('\u0026','&', $curTemp);
            	$curTemp = urldecode($curTemp);
            	if ($curTemp <> "") {
            		$curList = explode("&",$curTemp);
            		foreach ($curList as $curl) {
            		$curl = trim(substr($curl, strpos($curl,'https')-strlen($curl)));
            			if ($curl <> "" ){
            				if (strpos($curl,'itag=37') || strpos($curl,'=m37') !== false) {$v1080p=$curl;}
            				if (strpos($curl,'itag=22') || strpos($curl,'=m22') !== false) {$v720p=$curl;}
            				if (strpos($curl,'itag=18') || strpos($curl,'=m18') !== false) {$v360p=$curl;}
            			}
            		}
            		
            		if($v1080p){
                        $api[] = array(
                            'file'      => $v1080p,
                            'label'     => '1080p',
                            'type'      => 'video/mp4'
                        ); 
                        $api[] = array(
                            'file'      => $v720p,
                            'label'     => '720p',
                            'type'      => 'video/mp4',
                            'default'   => 'true'
                        ); 
                        $api[] = array(
                            'file'      => $v360p,
                            'label'     => '360p',
                            'type'      => 'video/mp4'
                        );                     
            		}
            		elseif($v720p){
                        $api[] = array(
                            'file'      => $v720p,
                            'label'     => '720p',
                            'type'      => 'video/mp4',
                            'default'   => 'true'
                        ); 
                        $api[] = array(
                            'file'      => $v360p,
                            'label'     => '360p',
                            'type'      => 'video/mp4'
                        );                         
            		} else {
                        $api[] = array(
                            'file'      => $v360p,
                            'label'     => '360p',
                            'type'      => 'video/mp4'
                        );             		    
            		}           		
            		
            	}
            }		    

			return json_encode($api);	
		}
		
        private function cut_str($str, $left, $right){
        	$str = substr(stristr($str, $left) , strlen($left));
        	$leftLen = strlen(stristr($str, $right));
        	$leftLen = $leftLen ? -($leftLen) : strlen($str);
        	$str = substr($str, 0, $leftLen);
        	return $str;
        }		
		
	}