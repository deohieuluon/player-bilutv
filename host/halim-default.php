<?php 
class halim_default extends HALIM_GetLink 
{
	public function get_link($link)
	{
		$data = HALIMHelper::cURL("http://api.phimhayplus.com/?url={$link}");

		//$json = json_decode($data); 
		//var_dump($json);
		//
		return $data;
	}
}