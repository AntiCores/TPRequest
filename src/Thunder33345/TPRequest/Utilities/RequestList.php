<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Utilities;

class RequestList
{

	/*
	$request[recipient][code] => Request
	*/
	private $requestList = [];
	private $timeout = 0;

	public function __construct(int $timeout)
	{
		$this->timeout = $timeout;
	}

	public function add(String $receiver, String $sender, bool $tpaTo):Request
	{
		$receiver = strtolower($receiver);
		$sender = strtolower($sender);

		$sendTime = time();
		$request = new Request($receiver, $sender, $tpaTo, $this->timeout, $sendTime);
		$this->requestList[$receiver][$sender] = $request;
		return $request;
	}

	/**
	 * @param String $receiver
	 *
	 * @return Request[]
	 */
	public function getAllFor(String $receiver):array
	{
		$receiver = strtolower($receiver);
		if(isset($this->requestList[$receiver]))
			return $this->requestList[$receiver];
		else return [];
	}

	/**
	 * @param String $sender
	 *
	 * @return Request[]
	 */
	public function getAllBy(String $sender):array
	{
		$sender = strtolower($sender);

		$result = [];
		foreach($this->requestList as $receiverName => $data){
			foreach($data as $senderName => $request){
				if(!$request instanceof Request) continue;//how lol
				if($senderName === $sender) $result[] = $request;
			}
		}
		return $result;
	}

	public function getByPair(String $receiver, String $sender):?Request
	{
		$receiver = strtolower($receiver);
		$sender = strtolower($sender);

		if(isset($this->requestList[$receiver][$sender])){
			return $this->requestList[$receiver][$sender];
		}
		return null;
	}

	public function getLastFor(String $receiver):?Request
	{
		$receiver = strtolower($receiver);

		$last = false;
		foreach($this->getAllFor($receiver) as $index => $request){
			if(!$last instanceof Request OR $request->getSendTime() > $last->getSendTime()){
				$last = $request;
			}
		}
		if(!$last instanceof Request){
			return null;
		}
		return $last;
	}

	/**
	 * @param String $receiver
	 * @param String $partialSender
	 * @param array &$invalidMatches
	 *
	 * @return Request[]
	 */
	public function matchRequestFor(String $receiver, String $partialSender, array &$invalidMatches = []):array
	{
		$receiver = strtolower($receiver);
		$partialSender = strtolower($partialSender);

		$list = $this->getAllFor($receiver);
		$matches = [];
		foreach($list as $request){
			if(substr(strtolower($request->getSender()), 0, strlen($partialSender)) == $partialSender){
				$matches[] = $request;
			}
		}
		$validMatches = [];
		foreach($matches as $request){
			if(!$request instanceof Request) continue;
			if($request->isValid()){
				$validMatches[] = $request;
			} else{
				$invalidMatches[] = $request;
			}
		}
		return $validMatches;
	}

	public function cleanInvalidFor(String $receiver)
	{
		$receiver = strtolower($receiver);
		foreach($this->requestList[$receiver] as $index => $request){
			if(!$request instanceof Request OR !$request->isValid()){
				unset($this->requestList[$receiver][$index]);
			}
		}
	}

	public function remove(String $receiver, String $sender):bool
	{
		$receiver = strtolower($receiver);
		$sender = strtolower($sender);

		if(isset($this->requestList[$receiver][$sender])){
			unset($this->requestList[$receiver][$sender]);
			return true;
		}
		return false;
	}

	public function removeRequest(Request $request)
	{
		unset($this->requestList[$request->getReceiver()][$request->getSender()]);
	}

	public function removeFor(String $receiver)
	{
		$receiver = strtolower($receiver);
		unset($this->requestList[$receiver]);
	}

	public function removeBy(String $sender)
	{
		$sender = strtolower($sender);

		foreach($this->requestList as $receiverName => $data){
			foreach($data as $senderName => $request){
				if(!$request instanceof Request) continue;//how lol
				if($senderName === $sender) unset($this->requestList[$receiverName][$senderName]);
			}
		}
	}
}