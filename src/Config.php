<?php
namespace Carawebs\OrganisePosts;


class Config implements \ArrayAccess {

    const OPTION = 'carawebs-organise-posts';
    const CAP    = 'manage_options';

    /**
     * @var \ArrayObject
     */
    private $stored;

    /**
     * @var \ArrayObject
     */
    private $live;

    /**
     * @param \Carawebs\OrganisePosts\Config $config
     * @param array                   $newData
     * @return \Carawebs\OrganisePosts\Config
     */
    public static function newInstanceFrom(Config $config = null, array $newData = [])
    {
        $live = $config ? $config->live->getArrayCopy() : [];
        $stored = $config ? array_merge($config->stored->getArrayCopy(), $newData) : $newData;
        $instance =  new static($live, []);
        $instance->stored = new \ArrayObject($stored);

        return $instance;
    }

    /**
     * @param array $liveConfig
     * @param array $defaults
     */
    public function __construct(array $liveConfig = [], array $defaults = [])
    {
        $stored = get_option(self::OPTION) ?: [];
        $this->stored = new \ArrayObject(array_merge($defaults, $stored));
        $liveConfig['capability'] = apply_filters('cookie-policy.config-capability', self::CAP);
        $this->live = new \ArrayObject($liveConfig);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->stored->getArrayCopy(), $this->live->getArrayCopy());
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (! is_admin() || ! current_user_can($this->live['capability'])) {
            return false;
        }

        $now =  get_option(self::OPTION, []);
        if ($now === $this->stored->getArrayCopy()) {
            return true;
        }

        return update_option(self::OPTION, $this->stored->getArrayCopy(), 'no');
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->stored->offsetExists($offset) || $this->live->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            throw new \BadMethodCallException($offset.' is not a valid config entry.');
        }

        return $this->stored->offsetExists($offset)
            ? $this->stored->offsetGet($offset)
            : $this->live->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            throw new \BadMethodCallException('Values in '.__CLASS__.' can\'t be modified.');
        }

        $this->stored->offsetSet($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Values in '.__CLASS__.' can\'t be modified.');
    }

}
