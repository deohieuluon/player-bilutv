<?php 
class halim_youtube_com extends HALIM_GetLink 
{
	function get_link($link)
	{
		$yt = new YouTubeDownloader();
		$json = $yt->getDownloadLinks($link);
		return json_encode($json);
	}	   
}