<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Utilities;

class CooldownList
{
	/*
	$cooldown[name] = future time
	*/
	private $cooldown = [];

	public function get(String $player):int
	{
		$player = strtolower($player);
		if(!isset($this->cooldown[$player])) return 0;
		$cd = $this->cooldown[$player];
		if($cd - time() <= 0){
			unset($this->cooldown[$player]);
			return 0;
		}
		return $cd - time();
	}

	public function add(String $player, int $seconds)
	{
		$player = strtolower($player);
		$cd = $this->get($player);
		$this->set($player, ($cd + $seconds));
	}

	public function set(String $player, int $seconds)
	{
		$player = strtolower($player);
		if($seconds <= 0){
			unset($this->cooldown[$player]);
			return;
		}
		$this->cooldown[$player] = $seconds;
	}
}