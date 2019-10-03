<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Thunder33345\TPRequest\Commands\TPAcceptCommand;
use Thunder33345\TPRequest\Commands\TPDenyCommand;
use Thunder33345\TPRequest\Commands\TPHereRequestCommand;
use Thunder33345\TPRequest\Commands\TPIgnoreCommand;
use Thunder33345\TPRequest\Commands\TPListCommand;
use Thunder33345\TPRequest\Commands\TPRequestCommand;
use Thunder33345\TPRequest\Utilities\CooldownList;
use Thunder33345\TPRequest\Utilities\IgnoreList;
use Thunder33345\TPRequest\Utilities\RequestList;

class TPRequest extends PluginBase implements Listener
{

	/**
	 * @var CooldownList $cooldownList
	 */
	private $cooldownList;
	/**
	 * @var RequestList $requestList
	 */
	private $requestList;
	/**
	 * @var IgnoreList $ignoreList
	 */
	private $ignoreList;

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		$ignoreList = new Config($this->getDataFolder() . 'ignorelist.json', Config::JSON, []);
		$this->ignoreList = new IgnoreList($ignoreList);
		$timeout = $this->getConfig()->getNested('request.timeout', 120);
		if(!is_int($timeout)) $timeout = 120;
		$this->requestList = new RequestList($timeout);
		$this->cooldownList = new CooldownList();

		$commandMap = $this->getServer()->getCommandMap();
		$tpaRequest = new TPRequestCommand($this);
		$commandMap->register($this->getName(), $tpaRequest);
		$commandMap->register($this->getName(), new TPHereRequestCommand($this, $tpaRequest));
		$commandMap->register($this->getName(), new TPAcceptCommand($this));
		$commandMap->register($this->getName(), new TPListCommand($this));
		$commandMap->register($this->getName(), new TPDenyCommand($this));
		$commandMap->register($this->getName(), new TPIgnoreCommand($this));


		$this->getServer()->getPluginManager()->registerEvents($this, $this);

	}

	public function onDisable()
	{

	}

	public function EventPlayerLeave(PlayerQuitEvent $playerQuitEvent)
	{
		//possible memleak, cooldown not unset
		$this->getRequestList()->removeFor($playerQuitEvent->getPlayer()->getName());
		$this->getRequestList()->removeBy($playerQuitEvent->getPlayer()->getName());
	}

	public function getIgnoreList(){ return $this->ignoreList; }

	public function getCooldownList(){ return $this->cooldownList; }

	public function getRequestList(){ return $this->requestList; }

	static private function parseSecondToHuman($seconds):?string
	{
		$dt1 = new \DateTime("@0");
		$dt2 = new \DateTime("@$seconds");
		$diff = $dt1->diff($dt2);
		if($diff === false) return null;
		$timeFrames = ['y' => 'year', 'm' => 'month', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second'];
		$str = [];
		foreach($timeFrames as $key => $name){
			if($diff->{$key} > 0) $str[] = $diff->{$key} . ' ' . $name . ($diff->{$key} > 1 ? 's' : '');
		}
		if(count($str) > 0){
			$str = implode(', ', $str);
		} else {
			$str = $diff->s . ' second';
		}
		return $str;
	}

	static private function colorize(array $string, array $colors)//lol
	{
		return $colors[0] . $string[0] . $colors[1] . $string[1] . $colors[0] . $string[2];
	}

	static private function TPR($color)
	{
		return TPRequest::colorize(['[', 'TPR', ']'], [$color, TextFormat::GOLD]);
	}

	static public function PREFIX()
	{
		return TPRequest::TPR(TextFormat::BLUE) . TextFormat::RESET;
	}

	static public function PREFIX_ERROR()
	{
		return TextFormat::YELLOW . '(!)' . self::PREFIX();
	}

	static public function PREFIX_OUTGOING()
	{
		return self::TPR(TextFormat::GREEN) . self::colorize(['(', 'X->', ')'], [TextFormat::GOLD, TextFormat::GREEN]) . TextFormat::RESET;
	}


	static public function PREFIX_INCOMING()
	{
		return self::TPR(TextFormat::RED) . self::colorize(['(', 'X<-', ')'], [TextFormat::GOLD, TextFormat::RED]) . TextFormat::RESET;
	}

	static public function PREFIX_COOLDOWN()
	{
		return TextFormat::RED . '(X)' . self::PREFIX();
	}

	static public function SUFFIX_MODE(bool $tpaTo)
	{
		if($tpaTo){
			$mid = 'TR';
			$midc = TextFormat::BLUE;
		} else {
			$mid = 'TH';
			$midc = TextFormat::YELLOW;
		}
		return self::colorize(['(', $mid, ')'], [TextFormat::GOLD, $midc]);
	}

	//const PREFIX = 'toberemoved';

}