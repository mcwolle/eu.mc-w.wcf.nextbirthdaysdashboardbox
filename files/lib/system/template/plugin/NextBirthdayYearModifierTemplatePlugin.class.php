<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template modifier plugin which returns the year of the next birthday.
 * 
 * Usage:
 * 	{$birthday|nextBirthdayYear}
 * 	{"1970-01-01"|nextBirthdayYear}
 * 
 * @author	Thomas Abröll
 * @copyright	2015 Thomas Abröll
 * @license	http://opensource.org/licenses/mit-license.php MIT
 * @package	eu.mc-w.wcf.nextbirthdaysdashboardbox
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class NextBirthdayYearModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// split date
		$month = $day = 0;
		$value = explode('-', $tagArgs[0]);
		if (isset($value[1])) $month = intval($value[1]);
		if (isset($value[2])) $day = intval($value[2]);

		$currentDate = new \DateTime();
		$year = $currentDate->format('Y');

		$birthday = new \DateTime();
		$birthday->setTimezone(WCF::getUser()->getTimeZone());
		$birthday->setDate($year, $month, $day);

		if ($birthday >= $currentDate) {
			return $year;
		} else {
			return $year + 1;
		}
	}
}
