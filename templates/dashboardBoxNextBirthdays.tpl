<ul class="sidebarBoxList">
	{foreach from=$birthdayUserProfiles item=birthdayUserProfile}
		<li class="box24">
			<a href="{link controller='User' object=$birthdayUserProfile}{/link}" class="framed">{@$birthdayUserProfile->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$birthdayUserProfile}{/link}" class="userLink" data-user-id="{@$birthdayUserProfile->userID}">{$birthdayUserProfile->username}</a></h3>
				<small>{$birthdayUserProfile->getBirthday()}</small>
			</div>
		</li>
	{/foreach}
</ul>

{if $birthdayUserProfiles|count >= 1}
	<a class="jsNextBirthdays button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			var $nextBirthdays = null;
			$('.jsNextBirthdays').click(function() {
				if ($nextBirthdays === null) {
					$nextBirthdays = new WCF.User.List('wcf\\data\\user\\UserNextBirthdaysAction', '{lang}wcf.dashboard.box.nextbirthdays{/lang}', { date: '{@TIME_NOW|date:'Y-m-d'}' });
				}
				$nextBirthdays.open();
			});
		});
		//]]>
	</script>
{/if}