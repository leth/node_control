<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_NetworkAdapter
 *
 * @Table(name="network_adapters")
 * @Entity
 */
class Model_NetworkAdapter extends Model_Entity
{
	/**
	 * @var text $mac
	 *
	 * @Column(name="mac", type="text", nullable=false)
	 */
	protected $mac;

	/**
	 * @var integer $wirelessChannel
	 *
	 * @Column(name="wireless_channel", type="integer", nullable=false)
	 */
	protected $wirelessChannel;

	/**
	 * @var text $type
	 *
	 * @Column(name="type", type="text", nullable=false)
	 */
	protected $type;

	/**
	 * @var Model_Node
	 *
	 * @ManyToOne(targetEntity="Model_Node")
	 * @JoinColumns({
	 *   @JoinColumn(name="node_id", referencedColumnName="id")
	 * })
	 */
	protected $node;

	public function toString()
	{
		$str  = "NetworkAdapter: {$this->id}, mac={$this->mac}, wirelessChannel={$this->wirelessChannel}, type={$this->type}";
		return $str;
	}
}
