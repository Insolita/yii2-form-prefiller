<?php
/**
 * Created by solly [29.01.17 0:39]
 */

namespace insolita\prefiller\storages;

use insolita\prefiller\contracts\IPrefillStorage;
use yii\di\Instance;
use yii\redis\Connection;

/**
 * Class RedisHashStorage
 *
 * @package insolita\prefiller\storages
 */
class RedisHashStorage extends BaseStorage implements IPrefillStorage
{
    /**
     * @var Connection $redis
     **/
    public $redis;
    
    /**
     * @var string
     */
    public $keyPrefix = '';
    
    /**
     * @var string
     */
    public $hashName = '';
    
    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::class);
    }
    
    /**
     * @inheritDoc
     */
    public function getValue(string $name, $default = null)
    {
        $value = $this->redis->executeCommand('HGET', [$this->hashName, $this->keyPrefix . $name]);
        return is_null($value) ? $default : $value;
    }
    
    /**
     * @inheritDoc
     */
    public function setValue(string $name, $value)
    {
        $this->redis->executeCommand('HSET', [$this->hashName, $this->keyPrefix . $name, $value]);
    }
}
