<?php

class Apiclient
{
	protected $uri;

	/**
	 * @param $apiuri
	 * @param $apiid
	 * @param $apikey
	 */
	public function __construct($apiuri, $apiid, $apikey)
	{
		$this->appid = $apiid;
		$this->appkey = $apikey;
		$this->apiurl = $apiuri;
	}

    // 调用API
	public function __call($method, $params = array())
	{
		$params=($params==array())?array():$params['0'];
		$params['action'] = $method;
		$params['appid'] = $this->appid;
		$params['format'] = 'json';
        $params['datetime']=$_SERVER['REQUEST_TIME'];
		$params['sign'] = $this->sign($params);
		//自动重试5次 防止失败！
		for($i=0;$i<5;$i++){
			$data = json_decode(http::get($this->apiurl, $params),true);
			if (is_array($data)){
				break;
			}
		}
		if (!empty($data) && is_array($data)){
			if ($data['status']==1){
				return $data['data'];
			}else{
				log::write('调用接口失败！原因：'.$data['msg']. ' 参数：' . var_export($params,true));
			}
		}else{
            log::write('调用接口失败！方法' . $method . ' 参数：' . var_export($params,true));
		}
        return array();
	}

    //对参数进行签名
	public function sign($params)
	{
		asort($params);
		$str='';
		foreach($params as $k=>$v){
			$str.=$k.'='.$v.'&';
		}
        $str=substr($str,0,-1);
		return md5($str.$this->appkey);
	}

}
