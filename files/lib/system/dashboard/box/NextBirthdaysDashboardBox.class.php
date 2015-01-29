<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfileList;
use wcf\page\IPage;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows birthdays of the next n days
 *
 * @author	Thomas Abröll
 * @copyright	2015 Thomas Abröll
 * @license	http://opensource.org/licenses/mit-license.php MIT
 * @package	eu.mc-w.wcf.nextbirthdaysdashboardbox
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class NextBirthdaysDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * user profiles
	 * @var	array<\wcf\data\user\UserProfile>
	 */
	public $userProfiles = array();

	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);

		// get current date
		$currentDay = DateUtil::format(null, 'm-d');

		// get user ids
		$date = new \DateTime();
		$userIDs = array();
		for ($i = 0; $i <= 7; $i++) {
			$extract = explode('-', DateUtil::format($date, 'Y-n-j'));
			$userIDs += UserBirthdayCache::getInstance()->getBirthdays($extract[1], $extract[2]);

			$date->add(new \DateInterval('P1D'));
		}

		if (!empty($userIDs)) {
			$userProfileList = new UserProfileList();
			$userProfileList->setObjectIDs($userIDs);
			$userProfileList->readObjects();
			$i = 0;
			foreach ($userProfileList as $userProfile) {
				if ($i == 5) break;

				if (!$userProfile->isProtected() && substr($userProfile->birthday, 5) == $currentDay) {
					$this->userProfiles[] = $userProfile;
					$i++;
				}
			}
		}

		$this->fetched();
	}

	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (empty($this->userProfiles)) {
			return '';
		}

		WCF::getTPL()->assign(array(
			'birthdayUserProfiles' => $this->userProfiles
		));
		return WCF::getTPL()->fetch('dashboardBoxNextBirthdays');
	}
}
