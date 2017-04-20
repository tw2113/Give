<?php
/**
 * Session
 *
 * @package     Give
 * @subpackage  Classes/Give_Session
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Session Class
 *
 * @since 1.0
 */
class Give_Session {

	/**
	 * @var mixed|null
	 */
	public $wpdb = null;

	/**
	 * @var string
	 */
	public $table;

	/**
	 * @var int
	 */
	public $version = 1;

	/**
	 * Give_Session constructor.
	 */
	public function __construct() {

		$this->wpdb = clone $GLOBALS['wpdb'];

		$this->table = $this->wpdb->prefix . 'give_sessions';

		$this->maybe_upgrade();
		session_set_save_handler(
			array( $this, 'open' ),
			array( $this, 'close' ),
			array( $this, 'read' ),
			array( $this, 'write' ),
			array( $this, 'destroy' ),
			array( $this, 'gc' )
		);
		register_shutdown_function( 'session_write_close' );

		// Garbage cleanup
		if ( ! wp_next_scheduled( 'give_wpdb_sessions_gc' ) ) {
			wp_schedule_event( time(), 'hourly', 'give_wpdb_sessions_gc' );
		}
		add_action( 'give_wpdb_sessions_gc', array( $this, 'cron_gc' ) );

	}

	/**
	 * Open a session.
	 */
	public function open() {
		return true;
	}

	/**
	 * Close a session.
	 */
	public function close() {
		return true;
	}

	/**
	 * Alias for reading session data.
	 *
	 * @param string $id Session id.
	 *
	 * @return mixed Session data or null.
	 */
	public function get( $id ) {
		return $this->read( $id );
	}

	/**
	 * Read session data.
	 *
	 * @param string $id Session id.
	 *
	 * @return mixed Session data or null.
	 */
	public function read( $id ) {
		if ( ! $this->wpdb ) {
			return null;
		}

		$data = @$this->wpdb->get_var( $this->wpdb->prepare( "SELECT `data` FROM `{$this->table}` WHERE `id` = %s;", $id ) );

		return maybe_unserialize( $data );
	}

	/**
	 * Alias for write method.
	 *
	 * @param string $id Session id.
	 * @param string $data Session data (serialized for session storage).
	 *
	 * @return null
	 */
	public function set( $id, $data ) {
		return $this->write( $id, $data );
	}

	/**
	 * Write a session.
	 *
	 * @param string $id Session id.
	 * @param string $data Session data (serialized for session storage).
	 *
	 * @return null
	 */
	public function write( $id, $data ) {
		if ( ! $this->wpdb ) {
			return null;
		}

		return $this->wpdb->query( $this->wpdb->prepare( "REPLACE INTO `{$this->table}` VALUES ( %s, %s, %d );", $id, maybe_serialize( $data ), time() ) );
	}

	/**
	 * Destroy a session.
	 *
	 * @param string $id Session id.
	 *
	 * @return bool
	 */
	public function destroy( $id ) {
		if ( ! $this->wpdb ) {
			return false;
		}

		return (bool) $this->wpdb->query( $this->wpdb->prepare( "DELETE FROM `{$this->table}` WHERE `id` = %s;", $id ) );
	}

	/**
	 * Garbage collection.
	 *
	 * @param $max
	 *
	 * @return bool
	 */
	public function gc( $max ) {
		return true;
	}

	/**
	 * Compare versions and maybe run an upgrade routine.
	 */
	public function maybe_upgrade() {

		$current_version = (int) get_site_option( 'give_wpdb_sessions_version', 0 );

		if ( version_compare( $this->version, $current_version, '>' ) ) {
			$this->do_upgrade( $current_version );
		}
	}

	/**
	 * Perform an upgrade routine.
	 *
	 * @param int $current_version The version number from which to perform the upgrades.
	 */
	public function do_upgrade( $current_version ) {
		global $wpdb;
		if ( $current_version < 1 ) {
			$wpdb->query( "CREATE TABLE `{$this->table}` (
				`id` varchar(255) NOT NULL,
				`data` mediumtext NOT NULL,
				`timestamp` int(255) NOT NULL,
				PRIMARY KEY (`id`)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;" );
			$current_version = 1;
			update_site_option( 'give_wpdb_sessions_version', $current_version );
		}
	}

	/**
	 * Cron-powered garbage collection.
	 */
	public function cron_gc() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$this->table}` WHERE `timestamp` < %d;", time() - HOUR_IN_SECONDS * 24 ) );
	}


}
