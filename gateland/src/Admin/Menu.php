<?php

namespace Nabik\Gateland\Admin;

defined( 'ABSPATH' ) || exit;

class Menu {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 20 );
		add_action( 'admin_head', [ $this, 'admin_head' ], 20 );

		Settings::instance();
	}

	public function admin_menu() {
		global $admin_page_hooks;

		$capability = apply_filters( 'nabik_menu_capability', 'manage_options' );

		if ( ! isset( $admin_page_hooks['nabik'] ) ) {
			add_menu_page( 'نابیک', 'نابیک', $capability, 'nabik', null, GATELAND_URL . 'assets/images/nabik.png', '55.9' );
		}

		$capability = apply_filters( 'nabik/gateland/menu_capability', 'manage_options' );

		add_menu_page( 'گیت‌لند', 'گیت‌لند', $capability, 'gateland', null, GATELAND_URL . 'assets/images/gateland.png', '55.19' );

		$submenus = [
			10 => [
				'title'      => 'پیشخوان',
				'capability' => $capability,
				'slug'       => 'gateland',
				'callback'   => function () {
					include GATELAND_DIR . '/templates/admin/dashboard.php';
				},
			],
			20 => [
				'title'      => 'تراکنش‌ها',
				'capability' => $capability,
				'slug'       => 'gateland-transactions',
				'callback'   => function () {
					include GATELAND_DIR . '/templates/admin/transactions.php';
				},
			],
			30 => [
				'title'      => 'درگاه‌ها',
				'capability' => $capability,
				'slug'       => 'gateland-gateways',
				'callback'   => function () {
					include GATELAND_DIR . '/templates/admin/gateways.php';
				},
			],
			40 => [
				'title'      => 'افزونه‌ها',
				'capability' => $capability,
				'slug'       => 'gateland-plugins',
				'callback'   => function () {
					include GATELAND_DIR . '/templates/admin/plugins.php';
				},
			],
			50 => [
				'title'      => 'تنظیمات',
				'capability' => $capability,
				'slug'       => 'gateland-settings',
				'callback'   => [ 'Nabik\Gateland\Admin\Settings', 'output' ],
			],
		];

		if ( ! defined( 'GATELAND_PRO_VERSION' ) ) {
			$submenus[60] = [
				'title'      => 'نسخه حرفه‌ای',
				'capability' => $capability,
				'slug'       => 'https://l.nabik.net/gateland-pro?utm_source=menu',
				'callback'   => '',
			];
		}

		$submenus = apply_filters( 'nabik/gateland/submenus', $submenus );

		foreach ( $submenus as $submenu ) {
			add_submenu_page( 'gateland', $submenu['title'], $submenu['title'], $submenu['capability'], $submenu['slug'], $submenu['callback'] );
		}

		add_submenu_page( 'gateland-pages', 'افزودن درگاه', 'افزودن درگاه', $capability, 'gateland-gateways-add', function () {
			include GATELAND_DIR . '/templates/admin/gateways-add.php';
		} );

		add_submenu_page( 'gateland-pages', 'ویرایش درگاه', 'ویرایش درگاه', $capability, 'gateland-gateways-edit', function () {
			include GATELAND_DIR . '/templates/admin/gateways-edit.php';
		} );

		add_submenu_page( 'gateland-pages', 'مشاهده درگاه', 'مشاهده درگاه', $capability, 'gateland-gateway', function () {
			include GATELAND_DIR . '/templates/admin/gateway.php';
		} );

		add_submenu_page( 'gateland-pages', 'مشاهده تراکنش', 'مشاهده تراکنش', $capability, 'gateland-transaction', function () {
			include GATELAND_DIR . '/templates/admin/transaction.php';
		} );
	}

	public function admin_head() {
		?>
		<script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("a[href*='l.nabik.net']").attr('target', '_blank');
            });
		</script>
		<?php
	}


}
