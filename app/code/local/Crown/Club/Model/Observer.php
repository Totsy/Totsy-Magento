<?php
class Crown_Club_Model_Observer {

	/**
	 * Moves expired club members back to the non club members group.
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @since 0.1.0
	 * @return void
	 */
	public function removeExpiredClubMembers($schedule) {
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return;

		$clubModel = Mage::getModel('crownclub/club');

		$expiredMembers = $clubModel->getExpiredMembersOutOfGracePeriod();

		foreach ($expiredMembers as $expiredMember) {
			$customer = Mage::getModel('customer/customer')->load($expiredMember->getId());
			$clubModel->removeClubMember($customer)->sendClubMembershipCancelledEmail($customer);
		}
	}

	/**
	 * Warns expired club members that their payment has failed and their subscription account
	 * will be cancelled once it exits the grace period.
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @since 0.1.0
	 * @return void
	 */
	public function warnExpiredClubMembers($schedule) {
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return;

		$clubModel = Mage::getModel('crownclub/club');

		$expiredMembers = $clubModel->getExpiredMembersInGracePeriod();

		foreach ($expiredMembers as $expiredMember) {
			$customer = Mage::getModel('customer/customer')->load($expiredMember->getId());
			$clubModel->sendClubMembershipPaymentFailedEmail($customer);
		}
	}
}