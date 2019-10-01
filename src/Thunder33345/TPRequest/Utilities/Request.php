<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Utilities;

class Request
{
	private
		$receiver,//- the person who received it(they must accept)
		$sender,//- the person who sent it
		$sendTime = 0,//- the time the request was sent
		$tpaTo = true,//- the mode, true means s->r, or else r->s
		$timeOut = 60;//- how long should the request last

	public function __construct(String $receiver, String $sender, bool $tpaTo, int $timeout, int $sendTime = null)
	{
		$this->receiver = $receiver;
		$this->sender = $sender;
		$this->tpaTo = $tpaTo;

		$this->timeOut = $timeout;
		if($sendTime == null){
			$sendTime = time();
		}
		$this->sendTime = $sendTime;
	}

	public function isValid():bool
	{
		return time() < $this->sendTime + $this->timeOut;
	}

	public function getLastFor():int
	{
		return ($this->sendTime + $this->timeOut) - time();
	}

	public function getReceiver():String
	{
		return $this->receiver;
	}

	public function getSender():String
	{
		return $this->sender;
	}

	public function getSendTime():int
	{
		return $this->sendTime;
	}

	public function isTpaTo():bool
	{
		return $this->tpaTo;
	}

}