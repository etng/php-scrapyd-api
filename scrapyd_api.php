<?php
require_once dirname(__file__) . '/http_client.php';
class ScrapydApiClient extends HttpClient{
    function _request($url, $method='GET', $params=array(), $data=array(), $files=array(), $extra=array()) {
        $response = parent::_request($url, $method, $params, $data, $files, $extra);
        $json = json_decode($response['body'], true);
        if($json['status']=='ok'){
            unset($json['status']);
            return $json;
        } elseif($json['status'] == 'error'){
            throw new Exception($json['message']);
        }
    }

}
class ScrapydAPI{
    public static $default_endpoints=array(
        'list_projects'=> '/listprojects.json',
        'add_version'=> '/addversion.json',
        'delete_project'=> '/delproject.json',
        'list_jobs'=> '/listjobs.json',
        'list_spiders'=> '/listspiders.json',
        'cancel'=> '/cancel.json',
        'list_versions'=> '/listversions.json',
        'schedule'=> '/schedule.json',
        'delete_version'=> '/delversion.json',
        );

    protected $endpoints;
    function __construct($target='http://localhost:6800', $auth=null, $endpoints=array(), $client=null)
    {
        $this->target = $target;
        $this->endpoints = self::$default_endpoints;
        foreach($endpoints as $e=>$v){
            $this->endpoints[$e]=$v;
        }
        if(!$client){
            $client = new ScrapydApiClient();
            $client->auth = $auth;
        }
        $this->client = $client;
    }

    function _build_url($endpoint)
    {
        if(isset($this->endpoints[$endpoint])) {
            return rtrim($this->target, '/') .'/'. ltrim($this->endpoints[$endpoint], '/');
        }
        throw new Exception("no such endpoint {$endpoint}");
    }
    function list_spiders($project){
        $url = $this->_build_url('list_spiders') ;
        $params = compact('project');
        $json = $this->client->get($url, $params, array());
        return $json['spiders']  ;
    }
    function list_versions($project){
        $url = $this->_build_url('list_versions') ;
        $params = compact('project');
        $json = $this->client->get($url, $params, array());
        return $json['versions']  ;
    }
    function list_jobs($project){
        $url = $this->_build_url('list_jobs') ;
        $params = compact('project');
        $jobs = $this->client->get($url, $params, array());
        return $jobs  ;
    }
    function delete_project($project){
        $url = $this->_build_url('delete_project') ;
        $params = compact('project');
        $this->client->post($url, $params, array(), array());
        return True ;
    }
    function cancel($project, $job){
        $url = $this->_build_url('cancel') ;
        $params = compact('project', 'job');
        $json = $this->client->post($url, $params, array(), array());
        return $json['prevstate'] == 'running';
    }
    function add_version($project, $version, $egg){
        $url = $this->_build_url('add_version') ;
        $data = compact('project', 'version');
        $files = compact('egg');
        $json = $this->client->post($url, $data, $files, array());
        return $json['spiders']  ;
    }
    function delete_version($project, $version, $egg){
        $url = $this->_build_url('delete_version') ;
        $data = compact('project', 'version');
        $files = array();
        $this->client->post($url, $data, $files, array());
        return True                           ;
    }
    function list_projects(){
        $url = $this->_build_url('list_projects') ;
        $json = $this->client->get($url, array(), array());
        return $json['projects']  ;
    }
    function schedule($project, $spider, $settings=array(), $kwargs=array()){
        $url = $this->_build_url('schedule') ;
        $data = array_merge($kwargs, compact('project', 'spider'));
        $data['settings'] = '';
        $json = $this->client->post($url, $data, array(), array());
        return $json['jobid']  ;
    }
}