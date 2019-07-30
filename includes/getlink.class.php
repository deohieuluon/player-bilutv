<?php
class HALIM_GetLink {

    public function init($url = null){
        $this->set_url($url);
        $this->load_host(); 
    }

    public function set_url($url){
        $this->_url = $url;
    }

    public function get_url(){
        return $this->_url;
    }

    public function load_host()
    {
        if(isset($this->_url)) 
        {        
            $info = parse_url($this->_url);
            $host = (isset($info['host'])) ? $info['host'] : 'default';
            $host = str_replace('www.', '', $host);
            if(file_exists(HALIM_PATH . 'host/halim-' . $host . '.php')){
                include_once HALIM_PATH . 'host/halim-' . $host . '.php';
                $class_name =  'halim_' . str_replace(array('-', '.'), '_', $host);
                $this->host = new $class_name; 
            }
            else {
                include_once HALIM_PATH . 'host/halim-default.php';
                $this->host = new halim_default;
            }
        }
    }
		
    public function get_content($url)
    {
        $ch = @curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $head[] = "Connection: keep-alive";
        $head[] = "Keep-Alive: 300";
        $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $head[] = "Accept-Language: en-us,en;q=0.5";
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_REFERER, HOST_NAME);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $page = curl_exec($ch);
        curl_close($ch);
        return $page;
    }
}