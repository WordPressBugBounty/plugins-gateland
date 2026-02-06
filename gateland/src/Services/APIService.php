<?php

namespace Nabik\Gateland\Services;

use Nabik\Gateland\API\DashboardAPI;
use Nabik\Gateland\API\GatewayAPI;
use Nabik\Gateland\API\PaymentAPI;
use Nabik\Gateland\API\PluginAPI;
use Nabik\Gateland\API\TransactionAPI;

class APIService {

	public function __construct() {
		new DashboardAPI();
		new GatewayAPI();
		new PaymentAPI();
		new PluginAPI();
		new TransactionAPI();
	}

}