<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Backfire_Tunnel extends Package_Config
{
	const package_name = "sown_openwrt_tunnel";

	public static $supported = array(
		'uci_config_openvpn' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_openvpn_v0_1_78'
			),
		),
	);

	public static function config_openvpn_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;
		$mod[] = $node;
		$mod[] = $node->vpnEndpoint;
		$mod[] = $node->vpnEndpoint->vpnServer;

		$config = array(
			'openvpn' => array(
				'sown_tunnel' => array(
					'enable' => 1,
					'client' => 1,
					
					'remote' => array(
						// Connect to the server by DNS name
						$node->vpnEndpoint->vpnServer->name .' '. $node->vpnEndpoint->port,
						// IP address failover incase of DNS lookup failure
						$node->vpnEndpoint->vpnServer->externalIPv4 .' '. $node->vpnEndpoint->port,
					),
					'proto' =>  $node->vpnEndpoint->protocol,
					
					// The server uses tap tunnels, so must you
					'dev' => 'tap',
					
					// Always try to reconnect
					'resolv_retry' => 'infinite',
					
					// No need to bind to a specific port
					'nobind' => 1,
					
					// Locations of SSL files
					'ca'   => '/etc/sown/ca.crt',
					'cert' => '/etc/sown/client.crt',
					'key'  => '/etc/sown/client.key',
					
					// Downgrade priveleges after initialization
					'user' => 'nobody',
					'group' => 'nogroup',
					
					// Try to preserve some state across restarts.
					'persist_key' => 1,
					'persist_tun' => 1,
					
					// Turn on some stuff for logging
					'verb' => 3,
					
					'script_security' => 2,
					
					// TODO port these scripts from vpn6Up and vpn6Down
					'up'   => '/etc/sown/events/tunnel_up',
					'down' => '/etc/sown/events/tunnel_down'
				),
			)
		);

		static::send_uci_config('openvpn', $config, $mod);
	}
}
