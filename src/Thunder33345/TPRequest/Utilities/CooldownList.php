<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Utilities;

class CooldownList
{
	const TYPE_REQUEST = 1;
	const TYPE_ACCEPT = 2;
	/*
	$cooldown[name][type] = future time
	*/
	private $cooldown = [];

	public function get(String $player, int $type):int
	{
		$player = strtolower($player);
		if(!isset($this->cooldown[$player][$type])) return 0;
		$cd = $this->cooldown[$player][$type];
		if($cd - time() <= 0){
			unset($this->cooldown[$player][$type]);
			return 0;
		}
		return $cd - time();
	}

	public function add(String $player, int $type, int $seconds)
	{
		$player = strtolower($player);
		$cd = $this->get($player, $type);
		$this->set($player, $type, ($cd + $seconds));
	}

	public function set(String $player, int $type, int $seconds)
	{
		$player = strtolower($player);
		if($seconds <= 0){
			unset($this->cooldown[$player][$type]);
			return;
		}
		$this->cooldown[$player][$type] = time() + $seconds;
	}
}