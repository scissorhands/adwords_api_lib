<?php 
/**
* 
*/
require('/vendor/autoload.php');
require('/adwords/Api.php');
class Test
{
	private $adwords;
	function __construct()
	{
		$this->adwords = new Api();
	}

	public function get_accounts( $customer_id = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
    	return $this->adwords->get_accounts();
	}

	public function get_campaigns( $customer_id = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
    	return $this->adwords->get_campaigns();
	}

	public function generic_request( $customer_id = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
    	return $this->adwords->generic_request('CampaignService', ['Id','Name']);
	}

	public function budget_order( $customer_id = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
    	return $this->adwords->generic_request('BudgetOrderService', [
    		'BudgetOrderName',	
			'EndDateTime',	
			'Id',
			'SpendingLimit',	
			'StartDateTime',
			'Status',
		]);
	}

	public function budget( $customer_id = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
    	return $this->adwords->generic_request('BudgetService', [
    		'Amount',
			'BudgetId',
			'BudgetName',
			'BudgetReferenceCount',
			'BudgetStatus',
			'DeliveryMethod',
			'IsBudgetExplicitlyShared'
    	]);
	}

}
?>