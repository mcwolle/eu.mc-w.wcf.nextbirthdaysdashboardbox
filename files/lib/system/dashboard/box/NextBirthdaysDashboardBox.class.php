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
	 * set to true if the showAllButton must be displayed
	 * @var	boolean
	 */
	public $showAllButton = false;

	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);

		// get user ids
		$date = new \DateTime();
		$date->setTimezone(WCF::getUser()->getTimeZone());
		$year = DateUtil::format($date, 'Y');
		$userIDs = array();
		for ($i = 0; $i < WCF_NEXTBIRTHDAYS_DAYS_TO_SHOW; $i++) {
			$extract = explode('-', DateUtil::format($date, 'Y-n-j'));
			$userIDs = array_merge($userIDs, UserBirthdayCache::getInstance()->getBirthdays($extract[1], $extract[2]));

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

			foreach ($userProfileList->getObjects() as $userProfile) {
				if (!$userProfile->isProtected() && $userProfile->getAge($year) >= 0) {
					$i++;
					if ($i == WCF_NEXTBIRTHDAYS_MAX_BIRTHDAYS+1) {
						$this->showAllButton = true;
						break;
					}
					$this->userProfiles[] = $userProfile;
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
			'birthdayUserProfiles' => $this->userProfiles,
			'showAllButton' => $this->showAllButton
		));
		return WCF::getTPL()->fetch('dashboardBoxNextBirthdays');
	}
}
