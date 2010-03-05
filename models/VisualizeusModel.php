<?php
/*
 *    Copyright 2008-2009 Laurent Eschenauer and Alard Weisscher
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *  
 */
class VisualizeusModel extends SourceModel {

	protected $_name 	= 'visualizeus_data';

	protected $_prefix = 'visualizeus';

	protected $_search  = 'title';
	
	protected $_actor_key = 'username';
	
	protected $_update_tweet = "Added %d photos to vi.sualize.us %s"; 

	public function getServiceName() {
		return "Vi.sualize.us";
	}

	public function isStoryElement() {
		return true;
	}

	public function getServiceURL() {
		if ($username = $this->getProperty('username')) {
			return "http://vi.sualize.us/$username";
		}
		else {
			return "http://vi.sualize.us/";;
		}
	}

	public function getServiceDescription() {
		return "Vi.sualize.us is a picture bookmarking service.";
	}

	public function setTitle($id, $title) {
		$this->updateItem($id, array('title' => $title));
	}

	public function importData() {
		$items = $this->updateData(true);
		$this->setImported(true);
		return $items;
	}


	public function updateData($full=false) {
		$username = $this->getProperty('username');

		$pages		= $full ? 50 : 1;
		$result 	= array();
					
		for ($page = 1; $page<=$pages; $page++) {	
			$url = "http://vi.sualize.us/rss/$username?page=$page";
			
			if (!($data = $this->loadFeed($url))) {
				throw new Stuffpress_Exception("vi.sualize.us did not return any result for url: $url", 0);
			}

			if (!$data->get_item_quantity()) break;
				
			$items = $this->processItems($data);
						
			$result = array_merge($result, $items);
		}
		
		// Mark as updated (could have been with errors)
		$this->markUpdated();
		
		return $result;
	}

	
	private function processItems($items) {
		$result = array();
		foreach ($items->get_items() as $item) {
			$data		= array();
		
			$tags	= $this->tag_parser($item->get_id(false));
						
			$data['title'] 			= $item->get_title();
			//$data['link']				= $item->get_link();
			$data['link']				= $this->fetch_link($item->get_content());
			$data['photo_id']		= $this->fetch_id($data['link']);
			$data['img_url']	  = $item->get_enclosure()->get_thumbnail();
			$data['subject']	  = implode(' ', $tags);
			$data['pubDate']    = $item->get_date();
							
			$id = $this->addItem($data, strtotime($data['pubDate']), SourceItem::IMAGE_TYPE, $tags, false, false, $data['title']);
			if ($id) $result[] = $id;
		}
		
		return $result;
	}
	
	private function loadFeed($url) {
		// I prefer simplepie to zend_feed
		$feed = new SimplePie();
		$feed->set_feed_url($url);
		$feed->set_timeout(30);
		$feed->set_cache_location($_SERVER['DOCUMENT_ROOT'] . '/temp');
		$feed->set_cache_duration(300);
		$feed->init();
		//$feed->handle_content_type();
				
		return $feed;
				
	}
	
	private function tag_parser($str) {
		$return = array();
		list($tmpID,$tag_list) = explode(":", $str);
		if ($tags=explode(",",$tag_list)) {
			$return = $tags;
		} else {
			$return[0] = $tag_list;
		}
		return $return;
	}
	
	private function fetch_id($str) {
		$tmp = explode("/",$str);
		return $tmp[5];
	}
	
	private function fetch_link($str) {
		$username = $this->getProperty('username');
		$regex = '/posted a new favorite picture \(\<a href\=\"(.*)\"\>via<\/a\>\)\:\<br \/\>/';
	  preg_match($regex,$str,$matches);
		echo $matches[1].chr(13);
		return $matches[1];
	}

}
