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

		// get user ids
		$date = new \DateTime();
		$userIDs = array();
		for ($i = 0; $i < 90; $i++) {
			$extract = explode('-', DateUtil::format($date, 'Y-n-j'));
			$newUserIDs = UserBirthdayCache::getInstance()->getBirthdays($extract[1], $extract[2]);
			if (count($newUserIDs) >= 1) {
				$userIDs[DateUtil::format($date, 'm-d')] = $newUserIDs;
			}

			$date->add(new \DateInterval('P1D'));
		}

		if (!empty($userIDs)) {
			$i = 0;
			foreach ($userIDs as $currentDay => $userIDsOfDay) {
				$userProfileList = new UserProfileList();
				$userProfileList->setObjectIDs($userIDsOfDay);
				$userProfileList->readObjects();

				foreach ($userProfileList as $userProfile) {
					if ($i == 5) break 2;

					if (!$userProfile->isProtected() && substr($userProfile->birthday, 5) == $currentDay) {
						$this->userProfiles[] = $userProfile;
						$i++;
					}
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
