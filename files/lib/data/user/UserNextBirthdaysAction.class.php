<?php
namespace wcf\data\user;
use wcf\data\IGroupedUserListAction;
use wcf\system\exception\UserInputException;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows a list of the next n user birthdays.
 *
 * @author	Thomas Abröll
 * @copyright	2015 Thomas Abröll
 * @license	http://opensource.org/licenses/mit-license.php MIT
 * @package	eu.mc-w.wcf.nextbirthdaysdashboardbox
 * @subpackage	data.user
 * @category	Community Framework
 */
class UserNextBirthdaysAction extends UserProfileAction implements IGroupedUserListAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getGroupedUserList');
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::validateGetGroupedUserList()
	 */
	public function validateGetGroupedUserList() {
		$this->readString('date');
		
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $this->parameters['date'])) {
			throw new UserInputException();
		}
	}
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::getGroupedUserList()
	 */
	public function getGroupedUserList() {
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
			$optionID = User::getUserOptionID('birthday');
			$userProfileList = new UserProfileList();
			$userProfileList->sqlOrderBy = 'SUBSTRING(user_option_value.userOption'.$optionID.', 6, 5), user_table.username';
			$userProfileList->setObjectIDs($userIDs);
			$userProfileList->readObjects();

			foreach ($userProfileList->getObjects() as $userProfile) {
				if (!$userProfile->isProtected() && $userProfile->getAge($year) >= 0) {
					$users[] = $userProfile;
				}
			}
		}
		
		WCF::getTPL()->assign(array(
			'users' => $users
		));
		return array(
			'pageCount' => 1,
			'template' => WCF::getTPL()->fetch('userNextBirthdaysList')
		);
	}
}
