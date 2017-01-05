<?php 
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201609\cm\CampaignService;
use Google\AdsApi\AdWords\v201609\cm\OrderBy;
use Google\AdsApi\AdWords\v201609\cm\Paging;
use Google\AdsApi\AdWords\v201609\cm\Selector;
use Google\AdsApi\Common\OAuth2TokenBuilder;

/**
* 
*/
class Api
{
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

	public function get_campaigns()
	{
		$adWordsServices = new AdWordsServices();
		$campaignService = $adWordsServices->get($this->session, CampaignService::class);

		$selector = new Selector();
		$selector->setFields([
			'Id',
			'Name'
		]);
		$selector->setOrdering([
			new OrderBy('Name', 'ASCENDING')
		]);
		$selector->setPaging(new Paging(0, 100));
		try {
			$page = $campaignService->get($selector);
			return $this->api_response( $page->getEntries() );
		} catch (Exception $e) {
			return $this->api_response( $e->getMessage, false);
		}
	}

	public function api_response($data = null, $success = true)
	{
		if($success){
			return (object)[
				'data' => $data,
				'status' => 'success'
			];
		} else {
			return (object)[
				'message' => $data,
				'status' => 'error'
			];
		}
	}
}
?>