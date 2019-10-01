<?php
declare(strict_types=1);

namespace Thunder33345\TPRequest\Utilities;

use pocketmine\Player;
use pocketmine\utils\Config;

/** Created By Thunder33345 **/
class IgnoreList
{

	private $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function __destruct()
	{
		$this->config->save();
	}

	public function isIgnored(Player $by, Player $whom)
	{
		$list = $this->getIgnore($by);
		return isset($list[$whom->getUniqueId()->toString()]);
	}

	public function ignore(Player $as, Player $to)
	{
		$ignore = $this->getIgnore($as);
		$to = $to->getUniqueId()->toString();
		$ignore[] = $to;
		$this->setIgnore($as, $ignore);
		return true;
	}

	public function unIgnore(Player $as, Player $to)
	{
		$ignore = $this->getIgnore($as);
		$to = $to->getUniqueId()->toString();
		unset($ignore[$to]);
		$this->setIgnore($as, $ignore);
		return true;
	}

	public function setIgnoreAll(Player $player, bool $status)
	{
		$this->config->setNested($player->getUniqueId()->toString() . '.all', $status);
		$this->config->save();
	}

	public function getIgnoreAll(Player $player):bool
	{
		return $this->config->getNested($player->getUniqueId()->toString() . '.all', false);
	}

	public function setAsTip(Player $player, bool $status)
	{
		$this->config->setNested($player->getUniqueId()->toString() . '.tip', $status);
		$this->config->save();
	}

	public function isAsTip(Player $player):bool
	{
		return $this->config->getNested($player->getUniqueId()->toString() . '.tip', true);
	}

	public function setIgnore(Player $player, array $data)
	{
		$this->config->setNested($player->getUniqueId()->toString() . '.list', $data);
		$this->config->save();
	}

	public function getIgnore(Player $player):array
	{
		return $this->config->getNested($player->getUniqueId()->toString() . '.list', []);
	}

}