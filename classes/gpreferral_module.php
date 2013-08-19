<?php
class GPReferral_Module extends Core_ModuleBase
{
	/**
	 * Creates the module information object
	 * @return Core_ModuleInfo
	 */
	protected function createModuleInfo()
	{
		return new Core_ModuleInfo(
			"Referrals",
			"Allows you to track where your customers came from",
			"GreatPotato"
		);
	}

	public function subscribeEvents()
	{
		Backend::$events->addEvent('shop:onExtendCustomerModel', $this, 'extend_customer_model');
		Backend::$events->addEvent('shop:onExtendCustomerForm', $this, 'extend_customer_form');
		
		Backend::$events->addEvent('shop:onBeforeOrderRecordCreate', $this, 'process_new_order');
	}

	public function extend_customer_model($customer, $context)
	{
		$customer->define_column('x_referral', 'Referral');
	}

	public function extend_customer_form($customer, $context)
	{
		if($context == 'preview') {
			$customer->add_form_field('x_referral')->tab('Customer');
		}
	}
	
	public function process_new_order($order, $session_key)
	{	
		// LemonStnad doesn't save the checkout data on the review step, so we have to do the following and re-save
		
		Shop_CheckoutData::save_custom_fields($_POST);
		$referral = Shop_CheckoutData::get_custom_field('x_referral');
		
		if( ! empty($referral) ) {
			$customer = Shop_Customer::create()->find($order->customer_id);
			$customer->x_referral = $referral;
			$customer->password = NULL;
			$customer->save();
		}
	}
}