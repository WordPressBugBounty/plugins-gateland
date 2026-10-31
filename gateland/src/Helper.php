<?php

namespace Nabik\Gateland;

use Nabik\Gateland\Helpers\IP;

defined( 'ABSPATH' ) || exit;

class Helper {

	public static function redirect( string $url ) {
		header( 'Content-type: text/html; charset=utf-8' );

		header( "Location: " . $url, true, 307 );

		echo "<html lang='fa'></html>";
		flush();
		ob_flush();
		exit;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function fa_num( string $string ): string {
		return str_replace( range( 0, 9 ), [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ], $string );
	}

	public static function mobile( string $mobile, $link = true ): string {

		$_mobile = str_replace( [ '+98', ' ' ], [ '0', '' ], $mobile );
		$_mobile = self::fa_num( $_mobile );

		if ( $link ) {
			$mobile = sprintf( '<a href="tel:%s" target="_blank">%s</a>', $mobile, $_mobile );
		} else {
			$mobile = $_mobile;
		}

		return $mobile;
	}

	/**
	 * @param string $dateTime
	 * @param string $format
	 *
	 * @return false|string
	 */
	public static function date( string $dateTime, string $format = 'Y/m/d H:i:s' ) {
		return wp_date( $format, strtotime( $dateTime ) );
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function en_num( string $string ): string {
		return str_replace( [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ], range( 0, 9 ), $string );
	}

	/**
	 * @return false|mixed|string
	 */
	public static function get_real_ip(): ?string {
		return IP::get();
	}
}