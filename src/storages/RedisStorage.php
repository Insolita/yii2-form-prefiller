<?php
/**
 * Created by solly [29.01.17 0:34]
 */

namespace insolita\prefiller\storages;

use insolita\prefiller\contracts\IPrefillStorage;
use yii\di\Instance;
use yii\redis\Connection;

/**
 * Class RedisStorage
 *
 * @package insolita\prefiller\storages
 */
class RedisStorage extends BaseStorage implements IPrefillStorage
{
    /**
     * @var Connection $redis
    **/
    public $redis;
    
    /**
     * @var int
     */
    public $ttl = 86400;
    /**
     * @var string
     */
    public $keyPrefix='';
    
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
        $value = $this->redis->get($this->keyPrefix.$name);
        return is_null($value)?$default:$value;
    }
    
    /**
     * @inheritDoc
     */
    public function setValue(string $name, $value)
    {
        $this->redis->set($this->keyPrefix.$name, $value);
        if($this->ttl){
            $this->redis->expire($this->keyPrefix.$name,$this->ttl);
        }
    }
    
}
