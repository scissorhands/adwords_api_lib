<?php 
/**
* 
*/
require('/vendor/autoload.php');
require('/adwords/Api.php');
use Google\AdsApi\AdWords\v201609\cm\AdType;
use Google\AdsApi\AdWords\v201609\cm\OrderBy;
use Google\AdsApi\AdWords\v201609\cm\Predicate;
use Google\AdsApi\AdWords\v201609\cm\AdGroupAdStatus;
use Google\AdsApi\AdWords\v201609\cm\PredicateOperator;
use Google\AdsApi\AdWords\v201609\cm\SortOrder;
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

	public function ad_group( $customer_id = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
    	return $this->adwords->generic_request('AdGroupService', [
    		"Id",
			"CampaignId",
			'Name'
    	]);
	}

	public function ad_group_ad( $customer_id = null, $campaign_id = null, $adGroupId = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
		$predicates = [
			new Predicate('AdGroupId', PredicateOperator::IN, [$adGroupId]),
			new Predicate('AdType', PredicateOperator::IN, [AdType::EXPANDED_TEXT_AD]),
			new Predicate('Status', PredicateOperator::IN,[
				AdGroupAdStatus::DISABLED, 
				AdGroupAdStatus::ENABLED,
				AdGroupAdStatus::PAUSED
			])
		];
		$sorting = [new OrderBy('HeadlinePart2', SortOrder::ASCENDING)];
    	return $this->adwords->generic_request('AdGroupAdService', [
				"Id",
				"CampaignId",
				"AdGroupId",
				'CreativeFinalUrls',
				'DisplayUrl',
				'Url',
				'Urls',
			], 
			$predicates,
			$sorting
    	);
	}

	public function ad_group_keywords( $customer_id = null, $campaign_id = null, $adGroupId = null )
	{
		if( $customer_id ){
			$this->adwords->set_session( $customer_id );
		}
		$predicates = [
		];
		$sorting = [];
    	return $this->adwords->generic_request('AdGroupCriterionService', [
				"FinalUrls",
				"Text"
			], 
			$predicates,
			$sorting
    	);
	}

}
?>