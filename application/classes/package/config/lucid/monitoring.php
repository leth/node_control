<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Lucid_Monitoring extends Package_Config
{
	const package_name = "sown_openwrt_monitoring";

	public static $supported = array(
		'config_nfsen' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_nfsen_v0_1_78'
			),
		),
		'config_icinga' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_icinga_v0_1_78'
			),
		),
	);

	public static function config_nfsen_v0_1_78(Model_Node $node = null)
	{
		if($node === null)
		{
			$repository = Doctrine::em()->getRepository('Model_Node');
			$data = "%sources = (\n";
			foreach($repository->findAll() as $node)
			{
				$fn =  __function__;
				$data .= static::$fn($node);
			}
			$data .= ")\n";
			static::send_file($data, 'nfsen-nodes.perl', 'text/perl');
		}
		return "'".$node->name."' => { 'port' => '".$node->vpnEndpoint->port."', 'col' => '#".substr(md5($node->name), 0, 6)."', 'type' => 'netflow' }\n";
	}

	public static function config_icinga_v0_1_78(Model_Node $node = null)
	{
		if($node === null)
		{
			$repository = Doctrine::em()->getRepository('Model_Node');
			$data = "";
			foreach($repository->findAll() as $node)
			{
				$fn =  __function__;
				$data .= static::$fn($node);
			}
			static::send_file($data, 'icinga-nodes.cfg', 'text/plain');
		}
		$email = "support@example.org";
		$name = $node->hostname;
		$alias = $node->name;
		$url = "";
		$latitude = $node->currentDeployment->latitude;
		$longitude = $node->currentDeployment->longitude;
		$range = $node->currentDeployment->range;
		$box_number = $node->boxNumber;
		$node_id = $node->id;
		foreach($node->interfaces as $i)
		{
			if($i->IPv4 != null)
			{
				$ipv4_addrs[] = $i->name."=".$i->IPv4->get_address();
			}
		}
		$address = implode(',', $ipv4_addrs);
		$vpn_endpoint = preg_replace('/\..*/', '', $node->vpnEndpoint->vpnServer->name);

		$o['alias'] = $alias;
		//$o['notes_url'] = $url;
		$o['2d_coords'] = $latitude.",".$longitude;
		$o['3d_coords'] = $latitude.",".$longitude.",".$range;
		$o['_BOXNUMBER'] = $box_number;
		$o['_NODEID'] = $node_id;
		$o['contacts'] = "+".$name."_admin";

		$use = "node";

		$hostgroups = "*Home Nodes,*OpenWRT Nodes";

		//$address = new Addr($ipv4_addrs);

		$parents = strtoupper(str_replace('sown-', '', $vpn_endpoint));
return "
define Contact {
	contact_name			{$name}_admin
	host_notification_period	24x7
	service_notification_period	24x7
	host_notification_options	d,r
	service_notification_options	n
	host_notification_commands	nodeadmin-notify-by-email
	service_notification_commands	notify-by-email
	email				{$email}
}

define Host {
	host_name	{$name}
	use		{$use}
	hostgroups	{$hostgroups}
	address		{$address}
	parents		{$parents}
	alias		{$alias}
	2d_coords	{$latitude},{$longitude}
	3d_coords	{$latitude},{$longitude},{$range}
	_BOXNUMBER	{$box_number}
	_NODEID		{$node_id}
	contacts	+{$name}_admin
}		
";
//	notes_url	{$url}
	}
}
