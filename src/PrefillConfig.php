<?php
/**
 * Created by solly [29.01.17 2:23]
 */

namespace insolita\prefiller;

use insolita\prefiller\contracts\IPrefillStorage;
use insolita\prefiller\storages\BaseStorage;
use yii\base\InvalidParamException;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class PrefillConfig
 *
 * @package insolita\prefiller
 */
class PrefillConfig extends BaseObject
{
    /**
     * @var null
     */
    public $formName = null;
    
    /**
     * @var string
     */
    public $method = 'post';
    
    /**
     * @var array
     */
    public $skipFromRequestAttributes = [];
    
    /**
     * @var array
     */
    public $skipFromStorageAttributes = [];
    
    /**
     * array in format ['name'=>'value'] or callable, that returns required format
     *
     * @var array|\Closure|callable
     **/
    public $defaultValues = [];
    
    /**
     * Override default component storage
     *
     * @var IPrefillStorage|null $storage
     **/
    public $storage = null;
    
    /**
     * If true - all model data will be serialized in one row (by JSON) and will be stored by $storagePrefix and
     * formName, else, each attribute  will be stored separated
     *
     * @var $serialized bool
     **/
    public $serialized = false;
    
    /**
     * Validate data retrieved from request
     *
     * @var bool
     */
    public $validateRequest = true;
    
    /**
     * Validate data retrieved from storage
     *
     * @var bool
     */
    public $validateStorage = false;
    
    /**
     * @throws \yii\base\InvalidParamException
     */
    public function init()
    {
        parent::init();
        if (!in_array($this->method, ['post', 'get'])) {
            throw new InvalidParamException('Method should be post or get');
        }
        if (is_callable($this->defaultValues)) {
            $this->defaultValues = call_user_func($this->defaultValues);
        }
        if (!is_null($this->storage)) {
            $this->storage = Instance::ensure($this->storage, BaseStorage::class);
        }
    }
    
    /**
     * @param $name
     *
     * @return mixed
     */
    public function getDefaultValue($name)
    {
        return ArrayHelper::getValue($this->defaultValues, $name, null);
    }
}
