<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Tasks;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Thunder33345\TPRequest\TPRequest;

class WarmupTask extends Task implements Listener
{
	private $tpRequest, $watched, $pair, $till, $summoning;

	private $effects = [], $radius = 5, $damage = 3;

	private $started = false, $damageTaken = 0, $center, $hasFailed = false;

	public function __construct(TPRequest $tpRequest, Player $watched, WarmupTask $pair, array $config, int $till, bool $summoning)
	{
		$this->tpRequest = $tpRequest;
		$this->watched = $watched;
		$this->pair = $pair;
		$this->till = $till;
		$this->summoning = $summoning;
		if(isset($config['effects']) and is_array($config['effects'])) $this->effects = $config['effects'];
		if(isset($config['radius']) and is_int($config['radius'])) $this->radius = $config['radius'];
		if(isset($config['damage']) and is_int($config['damage'])) $this->damage = $config['damage'];
	}

	public function onRun(int $currentTick)
	{
		if(!$this->started){
			$this->started = true;
			$this->onStart();
			return;
		}
		if($this->hasFailed){
			$this->onFailure();
			return;
		}
		if(time() > $this->till){
			$this->onComplete();
			return;
		}
		$this->onCheck();

	}

	private function onStart()
	{
		$player = $this->watched;
		$mode = "Teleporting";
		if($this->summoning) $mode = 'Summoning';
		$player->sendMessage($mode . ' request starting...');
		$player->sendMessage('Please try to stay within ' . $this->radius . ' radius, and dont take damage over' . $this->damage . ' times for ' . ($this->till - time()) . ' seconds');
		$this->center = $this->watched->asVector3();
	}

	private function onCheck()
	{
		//show tip progress bar
		if($this->watched->distance($this->center) > $this->radius){
			$this->hasFailed = true;
		}
	}

	private function onComplete()
	{
		$this->getHandler()->cancel();
		//execute tp
	}

	private function onFailure()
	{
		$this->getHandler()->cancel();
	}

	public function hasFailed(){ return $this->hasFailed; }
}