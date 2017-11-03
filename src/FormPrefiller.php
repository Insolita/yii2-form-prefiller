<?php
/**
 * Created by solly [29.01.17 2:01]
 */

namespace insolita\prefiller;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class FormPrefiller
 *
 * @package insolita\prefiller
 */
class FormPrefiller extends BasePrefiller
{
    
    /**
     * @inheritdoc
     */
    public function fill($model, PrefillConfig $config)
    {
        $requestData = $this->loadDataFromRequest($model, $config);
        
        $requestData = array_filter(
            $requestData,
            function ($key) use ($config) {
                return !in_array($key, $config->skipFromRequestAttributes);
            },
            ARRAY_FILTER_USE_KEY
        );
        $isByRequestUpdated = !empty($requestData)
            ? $this->fillFromRequest($requestData, $model, $config->validateRequest)
            : false;
        $this->fillFromStorage($model, $config, true);
        $this->fillDefaults($model, $config->defaultValues);
        if ($isByRequestUpdated === true) {
            $this->persist($model, $config);
        }
        unset($data);
        return $model;
    }
    
    /**
     * @inheritdoc
     */
    public function fillFromStorage($model, PrefillConfig $config, $skipIfSet = true)
    {
        $storage = !is_null($config->storage) ? $config->storage : $this->storage;
        if ($config->serialized === true) {
            $this->fillFromStorageSerialized($model, $config, $storage, $skipIfSet);
        } else {
            $this->fillFromStorageSeparated($model, $config, $storage, $skipIfSet);
        }
        return $model;
    }
    
    /**
     * @inheritdoc
     */
    public function persist($model, PrefillConfig $config)
    {
        $storage = !is_null($config->storage) ? $config->storage : $this->storage;
        if ($config->serialized === true) {
            $formName = $config->formName ?? $model->formName();
            $this->persistSerialized($model, $storage, $formName);
        } else {
            $this->persistSeparated($model, $storage);
        }
        return $model;
    }
    
    /**
     * @param \yii\base\Model                   $model
     * @param \insolita\prefiller\PrefillConfig $config
     *
     * @return mixed
     */
    private function loadDataFromRequest($model, PrefillConfig $config)
    {
        $method = $config->method;
        $scope = ($config->formName === null)
            ? $model->formName()
            : (!empty($config->formName)
                ? $config->formName
                : null);
        return $this->request->$method($scope);
    }
    
    /**
     * @param \yii\base\Model                               $model
     * @param \insolita\prefiller\contracts\IPrefillStorage $storage
     * @param string                                        $formName
     */
    private function persistSerialized($model, $storage, $formName)
    {
        $storage->setValue(
            $this->storagePrefix . $formName,
            Json::encode($model->getAttributes())
        );
    }
    
    /**
     * @param \yii\base\Model                               $model
     * @param \insolita\prefiller\contracts\IPrefillStorage $storage
     */
    private function persistSeparated($model, $storage)
    {
        foreach ($model->getAttributes() as $attribute => $value) {
            $storage->setValue($this->storagePrefix . $attribute, $value);
        }
    }
    
    /**
     * @param \yii\base\Model|\insolita\prefiller\contracts\IMemorableForm $model
     * @param \insolita\prefiller\contracts\IPrefillStorage                $storage
     * @param \insolita\prefiller\PrefillConfig                            $config
     * @param bool                                                         $skipIfSet
     */
    private function fillFromStorageSeparated($model, $config, $storage, $skipIfSet = true)
    {
        foreach ($model->getAttributes() as $attribute => $value) {
            if ($this->isNeedFillAttribute($model, $attribute, $skipIfSet, $config->skipFromStorageAttributes)) {
                $model->$attribute = $storage->getValue($this->storagePrefix . $attribute, null);
                if ($config->validateStorage && !$model->validate([$attribute])) {
                    $model->clearErrors($attribute);
                    $model->$attribute = null;
                }
            }
        }
    }
    
    /**
     * @param \yii\base\Model|\insolita\prefiller\contracts\IMemorableForm $model
     * @param \insolita\prefiller\PrefillConfig                            $config
     * @param \insolita\prefiller\contracts\IPrefillStorage                $storage
     * @param bool                                                         $skipIfSet
     */
    private function fillFromStorageSerialized($model, PrefillConfig $config, $storage, $skipIfSet = true)
    {
        $storageData = $storage->getValue($this->storagePrefix . ($config->formName ?? $model->formName()), []);
        $storageData = !empty($storageData) ? Json::decode($storageData) : [];
        
        foreach ($model->getAttributes() as $attribute => $value) {
            if ($this->isNeedFillAttribute($model, $attribute, $skipIfSet, $config->skipFromStorageAttributes)) {
                $model->$attribute = ArrayHelper::getValue($storageData, $attribute, null);
                if ($config->validateStorage && !$model->validate([$attribute])) {
                    $model->clearErrors($attribute);
                    $model->$attribute = null;
                }
            }
        }
    }
    
    /**
     * @param   \yii\base\Model|\insolita\prefiller\contracts\IMemorableForm $model
     * @param                                                                $attribute
     * @param                                                                $skipIfSet
     * @param array                                                          $skipAttributes
     *
     * @return bool
     */
    private function isNeedFillAttribute($model, $attribute, $skipIfSet, $skipAttributes = [])
    {
        if (!in_array($attribute, $skipAttributes)) {
            if (is_null($model->$attribute)) {
                return true;
            } elseif ($skipIfSet === false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param \yii\base\Model|\insolita\prefiller\contracts\IMemorableForm $model
     * @param                                                              $defaults
     */
    private function fillDefaults($model, $defaults)
    {
        if (is_callable($defaults)) {
            $defaults = call_user_func($defaults);
        }
        if (!empty($defaults) && is_array($defaults)) {
            foreach ($defaults as $attribute => $value) {
                if ($model->hasProperty($attribute) && is_null($model->$attribute)) {
                    $model->$attribute = $value;
                }
            }
        }
    }
    
    /**
     * @param array                                               $requestData
     * @param  Model|\insolita\prefiller\contracts\IMemorableForm $model
     * @param bool                                                $validate
     *
     * @return bool - flag indicated that request update model
     */
    private function fillFromRequest(array $requestData, $model, $validate)
    {
        $updated = false;
        foreach ($model->getAttributes() as $attribute => $value) {
            if (isset($requestData[$attribute])) {
                $model->$attribute = $requestData[$attribute];
                if ($validate === true && !$model->validate([$attribute])) {
                    $model->clearErrors($attribute);
                    $model->$attribute = null;
                } else {
                    $updated = true;
                }
            }
        }
        return $updated;
    }
    
}
