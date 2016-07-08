<?php
namespace Carawebs\OrganisePosts;


class Config implements \ArrayAccess {

    const OPTION = 'carawebs_organiser';
    const CAP    = 'manage_options';

    /**
     * @var \ArrayObject
     */
    public $container;

    /**
     * @param array $liveConfig
     * @param array $defaults
     */
    public function __construct( array $liveConfig = [], array $defaults = [] ) {

      $this->container = get_option( self::OPTION ) ?: [];   // Fetch option from the DB

    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) {

      return isset($this->container[$offset]) ? $this->container[$offset] : NULL;

    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset) {

      return isset($this->container[$offset]) ? $this->container[$offset] : null;

    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value) {

      if (is_null($offset)) {

        $this->container[] = $value;

      } else {

        $this->container[$offset] = $value;

      }

    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset) {

        unset($this->container[$offset]);

    }

}
