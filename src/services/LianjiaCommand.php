<?php

namespace app\services;

use Yii;
use app\library\Service;
use Symfony\Component\DomCrawler\Crawler;

class LianjiaCommand extends Service {
	
	private $_url = 'https://bj.lianjia.com%s%s%s/';
	
	private $_cities = [
		"/ershoufang/dongcheng/"       => "东城",
		"/ershoufang/xicheng/"         => "西城",
		"/ershoufang/chaoyang/"        => "朝阳",
		"/ershoufang/haidian/"         => "海淀",
		"/ershoufang/fengtai/"         => "丰台",
		"/ershoufang/shijingshan/"     => "石景山",
		"/ershoufang/tongzhou/"        => "通州",
		"/ershoufang/changping/"       => "昌平",
		"/ershoufang/daxing/"          => "大兴",
		"/ershoufang/yizhuangkaifaqu/" => "亦庄开发区",
		"/ershoufang/shunyi/"          => "顺义",
		"/ershoufang/fangshan/"        => "房山",
		"/ershoufang/mentougou/"       => "门头沟",
		"/ershoufang/pinggu/"          => "平谷",
		"/ershoufang/huairou/"         => "怀柔",
		"/ershoufang/miyun/"           => "密云",
		"/ershoufang/yanqing/"         => "延庆",
	];
	
	private $_page_limit = 50;
	
	private $_page_params = 'pg%s';
	
	/**
	 * 200万以下、200到250,250到300,300到400
	 * 不地下室
	 * 不车库
	 */
	private $_other_limit = 'ng1nb1p1p2p3p4';
	
	private $_request_urls = [];
	
	private $_datetime = null;
	
	public function __construct() {
		parent::__construct();
		$this->_buildUrl();
		$this->_datetime = date('Y-m-d H:i:s', time());
		
		return $this;
	}
	
	/**
	 * _buildUrl
	 * 获取请求地址
	 */
	private function _buildUrl() {
		
		for($index = 1; $index <= $this->_page_limit; $index++) {
			foreach($this->_cities as $url => $name) {
				$page                  = sprintf($this->_page_params, $index);
				$this->_request_urls[] = sprintf($this->_url, $url, $page, $this->_other_limit);
			}
		}
	}
	
	public function start() {
		
		foreach($this->_request_urls as $url) {
			$html = $this->_downloadPage($url);
			
			$crawler = new Crawler($html);
			
			$crawler->filter('ul.sellListContent > li')->each(function(Crawler $node, $i) {
				$this->_saveList([
					'url'        => $node->filter('a.img')->attr('href'),
					'title'      => $node->filter('div.title')->text(),
					'address'    => $node->filter('div.address')->text(),
					'flood'      => $node->filter('div.flood')->text(),
					'tag'        => $node->filter('div.tag')->text(),
					'price_info' => $node->filter('div.priceInfo')->text(),
					'price'      => (int)$node->filter('div.priceInfo')->text(),
				]);
			});
			exit;
			
		}
	}
	
	private function _downloadPage($url) {
		
		$client = new \GuzzleHttp\Client();
		$retry  = 5;
		$result = '';
		while($retry-- > 0) {
			$res = $client->request('GET', $url);
			if(200 == $res->getStatusCode()) {
				$result = $res->getBody();
				break;
			}
		}
		
		return (string)$result;
	}
	
	private function _saveList($data) {
		
		$data = array_merge([
			'datetime' => $this->_datetime
		], $data);
		Yii::$app->db->createCommand()->insert('post_list_data', $data)->execute();
	}
}
