<?php 
class halim_drive_google_com extends HALIM_GetLink
{	
    function get_link($link)
    {  	    
    	$id = HALIMHelper::getDriveId($link);

    	$stream = HALIMHelper::cURL("http://phimkk.com/getM3u8File?driveId={$id}");

		$videoUrl = "http://phimkk.com/hls/{$stream}/{$stream}.playlist.m3u8";

		$result[] = array(
			'file' => $videoUrl,
			'label' => 'FULL HD',
			'type' => 'video/mp4' 
		);
		return json_encode($result);
    }
	
	// public function get_linkxxx($link)
	// {
	// 	$listsv = preg_split('/\r\n|[\r\n]/', get_option('halim_listsv'));
	// 	// $number = (count($listsv) > 2) ? (count($listsv)-1) : 1;
	// 	// $listsv = $this->halim_array_rand($listsv, $number);
	// 	// shuffle($listsv);
	// 	$url = $listsv[array_rand($listsv)];

	// 	$data = $this->get_content($url.'/main.php?url='.urlencode($link));

	// 	if($data || $data != '[]') 
	// 		return $data;				 
	// 	else 
	// 		return $this->get_link($link);		
	// }

	// public function halim_array_rand($array, $number = null)
	// {
	//     $requested = ($number === null) ? 1 : $number;
	//     $count = count($array);

	//     if ($requested > $count) {
	//         throw new \RangeException(
	//             "You requested {$requested} items, but there are only {$count} items available."
	//         );
	//     }

	//     if ($number === null) {
	//         return $array[array_rand($array)];
	//     }

	//     if ((int) $number === 0) {
	//         return [];
	//     }

	//     $keys = (array) array_rand($array, $number);

	//     $results = [];
	//     foreach ($keys as $key) {
	//         $results[] = $array[$key];
	//     }

	//     return $results;
	// }

	// public function get_linkxx($link)
	// {
	// 	$list_sv =[
	// 		'http://example.stream/main.php?url=',
	// 		'http://example.stream/main.php?url=',
	// 	];
	// 	$api = $list_sv[array_rand($list_sv)];
	// 	$data = $this->get_content($api.urlencode($link));		
	// 	if($data) { 
	// 		return $data;
	// 	} 
	// 	else
	// 	{
	// 		return $this->get_link_old($link); 
	// 	}					
	// }
	

	function getDownloadLink($fileId) {
		$driveUrl	= "https://drive.google.com/uc?id=".urlencode($fileId)."&export=download";
		$returnUrl = $this->parseUrl($driveUrl);
		return $returnUrl;
	}

	function parseUrl($url, $cookies = null) {
		$fileId = null;
		$idPos = strpos($url, 'id=');
		
		if ($idPos !== false) {
			$fileId = substr($url, $idPos+3);
			$fileId = substr($fileId, 0, strpos($fileId, '&'));
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if ($cookies != null && is_array($cookies) && count($cookies) > 0) {
			curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
		}
		
		$response = curl_exec($ch);
		
		$headers = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$headers = explode("\r\n", $headers);
		
		$redirect = null;
		$cookies = array();
		
		foreach ($headers as $header) {
			$delimeterPos = strpos($header, ':');
			if ($delimeterPos === false)
				continue;
			
			$key = trim(strtolower(substr($header, 0, $delimeterPos)));
			$value	= trim(substr($header, $delimeterPos+1));
			
			if ($key == 'location') {
				$redirect = $value;
			}
			
			if (strpos($key, 'cookie') !== false) {
				$cookies[] = substr($value, 0, strpos($value, ';'));
			}
		}
		
		if ($redirect == null) {
			$confirm = strpos($response, "confirm=");
			
			if ($confirm !== false) {
				$confirm = substr($response, $confirm, strpos($response, '"'));
				$confirm = substr($confirm, strpos($confirm, '=')+1);
				$confirm = substr($confirm, 0, strpos($confirm, '&'));
				
				$redirect = $this->parseUrl("https://drive.google.com/uc?export=download&confirm=".urlencode($confirm)."&id=".urlencode($fileId), $cookies);
			}
		}
		
		return $redirect;
	}  

	// function get_content($url)
 //    {
 //        $ch = @curl_init();
 //        curl_setopt($ch, CURLOPT_URL, $url);
 //        $head[] = "Connection: keep-alive";
 //        $head[] = "Keep-Alive: 300";
 //        $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
 //        $head[] = "Accept-Language: en-us,en;q=0.5";
 //        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36');
 //        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
 //        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
 //        curl_setopt($ch, CURLOPT_REFERER, 'https://domain.com');
 //        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 //        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
 //        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 //        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
 //        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
 //        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
 //        $page = curl_exec($ch);
 //        curl_close($ch);
 //        return $page;
 //    }
	
}