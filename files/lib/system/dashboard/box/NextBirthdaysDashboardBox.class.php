<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\User;
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
	 * maximum number of days to show
	 * @var	integer
	 */
	public $daysToShow = 90;
	
	/**
	 * maximum number of entries to show
	 * @var	integer
	 */
	public $maxBirthdays = 5;

	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);

		// get user ids
		$date = new \DateTime();
		$userIDs = array();
		$birthdays = array();
		for ($i = 0; $i < $this->daysToShow; $i++) {
			// select 5 more as necessary because of later birthday check
			if (count($userIDs) > $this->maxBirthdays + 5) break;
			
			$extract = explode('-', DateUtil::format($date, 'Y-n-j'));
			$userIDs = array_merge($userIDs, UserBirthdayCache::getInstance()->getBirthdays($extract[1], $extract[2]));
			$birthdays[] = DateUtil::format($date, 'm-d');

			$date->add(new \DateInterval('P1D'));
		}

		// get user profiles
		if (!empty($userIDs)) {
			$i = 0;
			$optionID = User::getUserOptionID('birthday');
			$userProfileList = new UserProfileList();
			$userProfileList->sqlOrderBy = 'SUBSTRING(user_option_value.userOption'.$optionID.', 6, 5), user_table.username';
			$userProfileList->setObjectIDs($userIDs);
			$userProfileList->readObjects();

			foreach ($userProfileList as $userProfile) {
				if ($i == $this->maxBirthdays) break;

				if (!$userProfile->isProtected() && in_array(substr($userProfile->birthday, 5), $birthdays, true)) {
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
