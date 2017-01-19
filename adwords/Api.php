<?php 
namespace Scissorhands\GoogleAdwords;
use Google\AdsApi\AdWords\Reporting\v201609\ReportDefinition;
use Google\AdsApi\AdWords\Reporting\v201609\DownloadFormat;
use Google\AdsApi\AdWords\Reporting\v201609\ReportDownloader;
use Google\AdsApi\AdWords\v201609\cm\CampaignService;
use Google\AdsApi\AdWords\v201609\cm\OrderBy;
use Google\AdsApi\AdWords\v201609\cm\Paging;
use Google\AdsApi\AdWords\v201609\cm\SortOrder;
use Google\AdsApi\AdWords\v201609\cm\Selector;
use Google\AdsApi\AdWords\v201609\mcm\ManagedCustomerService;
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\Common\OAuth2TokenBuilder;

/**
* 
*/
class Api
{
	const PAGE_LIMIT = 500;
	const SERVICE_CLASS_PATH = 'Google\AdsApi\AdWords\\';
	const LATEST_VERSION = 'v201609';
	private $session;
	private $oAuth2Credential;
	private $version;

	function __construct()
	{
		$this->oAuth2Credential = (new OAuth2TokenBuilder())
		->fromFile()->build();
		$this->set_session();
		$this->set_version(Api::LATEST_VERSION);
	}

	private function get_service_class_path()
	{
		return Api::SERVICE_CLASS_PATH.$this->version.'\\';
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

	public function set_version( $version )
	{
		$this->version = $version;
	}

	public function get_accounts()
	{
		$adWordsServices = new AdWordsServices();
		$managedCustomerService = $adWordsServices->get( $this->session, ManagedCustomerService::class);

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

	public function generic_request( $serviceClassSubPath, $fields = [], $predicates = [], $sorting = [])
	{	
		$service = $this->get_service( $serviceClassSubPath );

		$selector = new Selector();
		$selector->setFields($fields);
		if( $sorting ){
			$selector->setOrdering( $sorting );
		}
		if( $predicates ){
			$selector->SetPredicates( $predicates );
		}
		$selector->setPaging(new Paging(0, self::PAGE_LIMIT));

		$data = [];
		$totalNumEntries = 0;
		do {
			try {
				$page = $service->get($selector);
			} catch (Exception $e) {
				return $this->api_response( $e->getMessage(), false);
			}
			if ($page->getEntries() !== null) {
				$totalNumEntries = $page->getTotalNumEntries();
				foreach ($page->getEntries() as $requestData) {
					$data[] = $requestData;
				}
			}
			$selector->getPaging()->setStartIndex($selector->getPaging()->getStartIndex() + self::PAGE_LIMIT);
		} while ($selector->getPaging()->getStartIndex() < $totalNumEntries);
		return $this->api_response( $data );
	}

	public function raw_request( $serviceClassSubPath, $fields = [], $predicates = [], $sorting = [] )
	{
		$service = $this->get_service( $serviceClassSubPath );

		$selector = new Selector();
		$selector->setFields($fields);
		if( $sorting ){
			$selector->setOrdering( $sorting );
		}
		if( $predicates ){
			$selector->SetPredicates( $predicates );
		}
		$selector->setPaging(new Paging(0, self::PAGE_LIMIT));

		$data = [];
		$totalNumEntries = 0;
		try {
			$request = $service->get($selector);
		} catch (Exception $e) {
			return $this->api_response( $e->getMessage(), false);
		}
		return $this->api_response( $request );
	}

	private function get_service( $serviceClassSubPath )
	{
		$adWordsServices = new AdWordsServices();
		return $adWordsServices->get($this->session, $this->get_service_class_path().
			str_replace('/', '\\', $serviceClassSubPath )
		);
	}

	public function report( $reportType, $fields, $dateRangeType, $predicated = null, $dateRange = null )
	{
		$selector = new Selector();
		$selector->setFields( $fields );

		if( $predicated ){
			$selector->setPredicates($predicated);
		}
		if( $dateRangeType == 'CUSTOM_DATE' ){
			$selector->setDateRange( $dateRange );
		}

		$reportDefinition = new ReportDefinition();
		$reportDefinition->setSelector($selector);
		$reportDefinition->setReportName("{$reportType}: '{$this->session->getClientCustomerId()}' ".uniqid() );
		$reportDefinition->setDateRangeType( $dateRangeType );
		$reportDefinition->setReportType( $reportType );
		$reportDefinition->setDownloadFormat(DownloadFormat::CSV);

		$reportDownloader = new ReportDownloader($this->session);
		try {
			$reportDownloadResult = $reportDownloader->downloadReport($reportDefinition);
		} catch (Exception $e) {
			return $this->api_response($e->getMessage(), false);
		}
		return $this->api_response(
			$this->csvStringToObjectArray( $reportDownloadResult->getAsString() )
		);
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

	private function csvStringToObjectArray( $csv, $add_field = null ) {
		$csv = str_replace( "%", "", $csv );
		$csv = str_replace( "Day", "date", $csv );
		$arr = str_getcsv( $csv, "\n" );
		unset( $arr[count( $arr )-1] );
		unset( $arr[0] );
		$patterns = array(); $replace = array();
		$patterns[] = "/[\/]/"; $replace[] = "";
		$patterns[] = "/\s\s+/"; $replace[] = " ";
		$patterns[] = "/\s/"; $replace[] = "_";
		$patterns[] = "/[.]/"; $replace[] = "";
		$row1 = strtolower( preg_replace($patterns, $replace, $arr[1]) );

		unset( $arr[1] );
		$csv_fields = explode( ",", $row1 );
		$stats = array();

		foreach ( $arr as $row ) {
			$values = str_getcsv( $row, ",", '"' );
			$obj = new \stdClass();
			foreach ( $values as $key => $value ) {
				$field = $csv_fields[$key];
				if ( $field == "cost" || $field == "avg_cpc" || $field == "budget" ) {
					$obj->$field = $value/1000000;
				}
				else {
					if( is_numeric($value) ){
						$obj->$field = (double) $value;
					}else{
						$obj->$field = $value;
					}
				}
			}
			if ( $add_field ) {
				$obj->$add_field["field"] = $add_field["value"];
			}
			$stats[] = $obj;
		}
		return $stats;
	}
}
?>