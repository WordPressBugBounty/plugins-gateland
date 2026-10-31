<?php

namespace Nabik\Gateland\Gateways;

use Exception;
use Nabik\Gateland\Enums\Transaction\CurrenciesEnum;
use Nabik\Gateland\Enums\Transaction\StatusesEnum;
use Nabik\Gateland\Exceptions\InquiryException;
use Nabik\Gateland\Exceptions\VerifyException;
use Nabik\Gateland\Gateways\Features\InquiryFeature;
use Nabik\Gateland\Gateways\Features\ShaparakFeature;
use Nabik\Gateland\Models\Transaction;

class TabaPayGateway extends BaseGateway implements \Nabik\Gateland\Gateways\Features\FreeFeature, InquiryFeature, ShaparakFeature {

	protected string $name = 'تاباپی';

	protected string $description = 'tabapay.ir';

	protected string $url = 'https://l.nabik.net/tabapay';

	public function request( Transaction $transaction ): void {
		$this->log( $transaction, 'request', [
			'transaction' => $transaction->toArray(),
		] );

		$parameters = [
			'amount'      => intval( $transaction->amount * 10 ),
			'callbackURL' => $transaction->gateway_callback,
			'description' => mb_substr( $transaction->description ?? '', 0, 300 ),
		];

		if ( $transaction->mobile ) {
			$parameters['mobile'] = str_replace( '+98', '0', $transaction->mobile );
		}

		$headers = [
			'Accept: application/json',
			'Authorization: Bearer ' . $this->options['merchant'],
		];

		try {

			$url      = 'https://api.tabapay.ir/v1/create';
			$response = $this->curl( $url, $parameters, $headers );

			$this->log( $transaction, 'paymentRequest', [
				'parameters' => $parameters,
				'headers'    => $headers,
				'response'   => $response,
			] );

		} catch ( Exception $e ) {

			$this->log( $transaction, 'requestFailed', [
				'parameters' => $parameters,
				'headers'    => $headers,
				'error'      => $e->getMessage(),
			] );

			throw new Exception( 'خطا در اتصال به درگاه! لطفا دوباره تلاش کنید.' );
		}

		if ( isset( $response['token'] ) ) {

			$transaction->update( [
				'gateway_au' => strval( $response['token'] ),
			] );

			return;
		}

		throw new Exception( sprintf( 'خطا %s: %s', $response['responseCode'], $response['message'] ) );
	}

	public function inquiry( Transaction $transaction ): bool {
		$this->log( $transaction, 'inquiry', [
			'transaction' => $transaction->toArray(),
		] );

		$responseCode = intval( $_GET['responseCode'] ?? 1 );

		if ( $responseCode == - 1 ) {
			return $this->cancelled( $transaction, $responseCode );
		}

		$parameters = [
			'token'  => $transaction->gateway_au,
			'amount' => intval( $transaction->amount * 10 ),
		];

		$headers = [
			'Accept: application/json',
			'Authorization: Bearer ' . $this->options['merchant'],
		];

		try {

			$url      = 'https://api.tabapay.ir/v1/verify';
			$response = $this->curl( $url, $parameters, $headers );

			$this->log( $transaction, 'verifyRequest', [
				'parameters' => $parameters,
				'headers'    => $headers,
				'response'   => $response,
			] );

		} catch ( Exception $e ) {

			$this->log( $transaction, 'requestFailed', [
				'parameters' => $parameters,
				'headers'    => $headers,
				'error'      => $e->getMessage(),
			] );

			throw new VerifyException();
		}

		$paid_statuses = [
			1, // Success - First verify
			2, // Success - Duplicate Verify
		];

		$is_paid = in_array( $response['responseCode'], $paid_statuses );

		if ( $is_paid ) {
			$this->log( $transaction, 'verifySuccess' );

			$transaction->update( [
				'gateway_trans_id' => $response['shaparakRefNumber'],
				'gateway_status'   => $response['responseCode'],
				'status'           => StatusesEnum::STATUS_PAID,
				'card_number'      => $response['cardNumber'],
				'paid_at'          => \Carbon\Carbon::now(),
			] );

			return true;
		}

		throw new InquiryException( $response['responseCode'] );
	}

	public function redirect( Transaction $transaction ) {
		$this->log( $transaction, 'redirect', [
			'transaction' => $transaction->toArray(),
		] );

		return wp_redirect( 'https://api.tabapay.ir/pay/' . $transaction->gateway_au );
	}

	public function currencies(): array {
		return [
			CurrenciesEnum::IRT,
		];
	}

	public function options(): array {
		return [
			[
				'label' => 'مرچنت',
				'key'   => 'merchant',
			],
		];
	}
}
