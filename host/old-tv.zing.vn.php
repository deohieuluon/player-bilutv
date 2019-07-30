<?php 
	class halim_tv_zing_vn extends HALIM_GetLink
	{
		function get_link($link)
		{
			$curl = $this->getZingVIP($link);

			if(preg_match('/hlsSupport/is', $curl))
			{				
				preg_match('/playlist.source = "(.*?)";/is', $curl, $hls); //hls streaming
				if($hls[1])
				{
		            $api[] = array(
	                    'file'      => $hls[1],
	                    'label'     => 'FULL HD',
	                    'type'      => 'hls'
	                );			
				} 
			}
			else
			{			
				preg_match_all('/source: "(.*?)"/is', $curl, $data);
				if($data[1])
				{
					$label = preg_match('/1080/is', $data[1][0]) ? array(0 => '1080p', 1 => '720p', 2 => '480p', 3 => '360p', 4 => '240p') : array(0 => '720p', 1 => '480p', 2 => '360p', 3 => '240p');
					foreach ($data[1] as $key => $value) {
		                $api[] = array(
	                        'file'      => $value,
	                        'label'     => $label[$key],
	                        'type'      => 'video/mp4',
	                        'default'	=> $label[$key] == '720p' ? true : false
	                    );					
					}
				} 
			}

			return json_encode($api);	
		}

		private function getZingVIP($service_url) {
		    $handle = curl_init($service_url);
		    curl_setopt_array($handle, array(
		        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1',
		        CURLOPT_ENCODING => 'utf8',
		        CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => array(get_option('halim_zing_cookie')),
		        CURLOPT_SSL_VERIFYPEER => 0,
		        CURLOPT_FOLLOWLOCATION => 1,
		       	CURLOPT_REFERER => 'http://tv.zing.vn'
		    ));
		    $curl_response = curl_exec($handle);
		    curl_close($handle);
		    return $curl_response;
		}
	}