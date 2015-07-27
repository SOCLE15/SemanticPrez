<?php

class FunctionalRequirement{
	private $title;
	private $techReqs;
	function __construct($title){
		$this->title = $title;
		$this->techReqs = array();
	}
	public function setTitle($title){
		$this->title = $title;
	}
	public function addTechReq($techReq){
		$this->techReqs[$techReq->getTitle()] = $techReq;
	}
	public function getTitle(){
		return $this->title;
	}
	public function techReqByTitle($title){
		$ret = null;
		if($this->hasTechReqByTitle($title)){
			$ret = $this->techReqs[$title];
		}
		return $ret;
	}
	public function hasTechReqByTitle($title){
		return array_key_exists($title, $this->techReqs);
	}
	public function getTechReqs(){
		return $this->techReqs;
	}

}

