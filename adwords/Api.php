<?php 
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201609\mcm\ManagedCustomerService;
use Google\AdsApi\AdWords\v201609\cm\CampaignService;
use Google\AdsApi\AdWords\v201609\cm\OrderBy;
use Google\AdsApi\AdWords\v201609\cm\Paging;
use Google\AdsApi\AdWords\v201609\cm\SortOrder;
use Google\AdsApi\AdWords\v201609\cm\Selector;
use Google\AdsApi\Common\OAuth2TokenBuilder;

/**
* 
*/
class Api
{
	const PAGE_LIMIT = 500;
	private $session;
	private $oAuth2Credential;
	function __construct()
	{
		$this->oAuth2Credential = (new OAuth2TokenBuilder())
		->fromFile()->build();
		$this->set_session();
	}

	public function set_session( $customer_id = null )
	{
		if($customer_id){
			$this->session = (new AdWordsSessionBuilder())
			->fromFile()
			->withOAuth2Credential($this->oAuth2Credential)
			->withClientCustomerId($customer_id)->build();
		} else {
			$this->session = (new AdWordsSessionBuilder())
			->fromFile()
			->withOAuth2Credential($this->oAuth2Credential)
			->build();
		}
	}

	public function get_accounts()
	{
		$adWordsServices = new AdWordsServices();
		$managedCustomerService = $adWordsServices->get( $this->session, ManagedCustomerService::class);

		// Create selector.
		$selector = new Selector();
		$selector->setFields([
			'CustomerId', 
			'Name',
			'CanManageClients',
			'CurrencyCode',
			'DateTimeZone'
		]);
		$selector->setOrdering([new OrderBy('CustomerId', SortOrder::ASCENDING)]);
		$selector->setPaging(new Paging(0, self::PAGE_LIMIT));

		$accounts = [];
		$totalNumEntries = 0;
		do {
			try {
				$page = $managedCustomerService->get($selector);
			} catch (Exception $e) {
				return $this->api_response( $e->getMessage(), false);
			}
			if ($page->getEntries() !== null) {
				$totalNumEntries = $page->getTotalNumEntries();
				foreach ($page->getEntries() as $account) {
					$accounts[] = $account;
				}
			}
			$selector->getPaging()->setStartIndex($selector->getPaging()->getStartIndex() + self::PAGE_LIMIT);
		} while ($selector->getPaging()->getStartIndex() < $totalNumEntries);
		return $this->api_response( $accounts );
	}

	public function get_campaigns()
	{
		$adWordsServices = new AdWordsServices();
		$campaignService = $adWordsServices->get($this->session, CampaignService::class);

		$selector = new Selector();
		$selector->setFields([
			'Id',
			'Name',
			'Status',
			'ServingStatus',
			'StartDate',
			'EndDate',
			'BudgetId',
			'TrackingUrlTemplate',
		]);
		$selector->setOrdering([
			new OrderBy('Name', 'ASCENDING')
		]);
		$selector->setPaging(new Paging(0, 100));
		try {
			$page = $campaignService->get($selector);
			return $this->api_response( $page->getEntries() );
		} catch (Exception $e) {
			return $this->api_response( $e->getMessage(), false);
		}
	}

	public function api_response($data = null, $success = true)
	{
		if($success){
			return (object)[
				'status' => 'success',
				'data' => $data,
			];
		} else {
			return (object)[
				'status' => 'error',
				'message' => $data,
			];
		}
	}
}
?>