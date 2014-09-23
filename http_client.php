<?php
//$client = new HttpClient();
//$client->post('http://httpbin.org/post', array(), array('source_file'=>realpath(__file__)), array('proxy'=>'localhost:8888'));
class HttpClient{
    public $auth;
    public $cookiefile;
    public $extra=array();
    public $ua ="Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
    function __construct(){
        $this->cookiefile = tempnam(sys_get_temp_dir(), uniqid("", true));
    }
    function get($url, $params=array(), $extra=array()) {
        return $this->_request($url, 'GET', $params, array(), array(), $extra);
    }
    function post($url, $data=array(), $files=array(), $extra=array()) {
       return $this->_request($url, 'POST', array(), $data, $files, $extra);

   }
   function _request($url, $method='GET', $params=array(), $data=array(), $files=array(), $extra=array()) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    if($method=='POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        if($files) {
            foreach($files as $k=>$file) {
                $data[$k] = '@'.$file;
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    } else {
        curl_setopt($curl, CURLOPT_POST, false);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, $this->ua);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookiefile);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookiefile);
    if($this->auth) {
        curl_setopt($curl, CURLOPT_USERPWD, implode(':', $this->auth));
    }
    foreach((array)$this->extra as $k=>$v) {
        curl_setopt($curl, constant('CURLOPT_'.strtoupper($k)), $v);
    }
    foreach($extra as $k=>$v) {
        curl_setopt($curl, constant('CURLOPT_'.strtoupper($k)), $v);
    }
    if($params){
        $conn = strpos($url, '?')===False?'?':'&';
        $url.=$conn.http_build_query($params);
    }
    curl_setopt($curl, CURLOPT_URL, $url);

    $body = curl_exec($curl);
    curl_close ($curl);
    $header_history=array();
    do{
        list($header, $body) = explode("\r\n\r\n", $body, 2);
        $headers = array();
        $protocal = 'HTTP/1.1';
        $status = 200;
        $status_message = '';
        foreach(explode("\r\n", $header) as $i=>$header_line){
            if($i==0){
               list($protocal, $status, $status_message) = explode(" ", $header_line, 3);
           }else{
            list($k, $v) = explode(": ", $header_line);
            $headers[$k]=$v;
            }
        }
        $header_history[]=compact('protocal','status','status_message','headers');
    }while($status>300 and $status<400);
    //        var_dump($protocal, $status, $status_message, $headers, $header_history, $body);
    //        return $body;
return compact('protocal','status','status_message','headers', 'header_history', 'body')    ;
}
}
