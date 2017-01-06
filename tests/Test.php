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
}
?>