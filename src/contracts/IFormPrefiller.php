<?php
/**
 * Created by solly [29.01.17 1:52]
 */

namespace insolita\prefiller\contracts;

use insolita\prefiller\PrefillConfig;
use yii\base\Model;

/**
 * Interface IFormPrefiller
 *
 * @package insolita\prefiller\contracts
 */
interface IFormPrefiller
{
    /**
     * Fill model in chain ->from request, next not filled or not valid - by storage, next not filled
     * - by config defaults and chain result will be persist in storage
     * @param Model|\insolita\prefiller\contracts\IMemorableForm
     * @param \insolita\prefiller\PrefillConfig $config
     * @return Model|\insolita\prefiller\contracts\IMemorableForm
     */
    public function fill($model, PrefillConfig $config);
    
    /**
     * Fill data or model attributes from storage
     *
     * @param Model|\insolita\prefiller\contracts\IMemorableForm $model
     * @param \insolita\prefiller\PrefillConfig $config
     * @param bool        $skipIfSet
     *
     * @return Model|IFormPrefiller
     */
    public function fillFromStorage($model, PrefillConfig $config, $skipIfSet = true);
    
    /**
     * @param Model|\insolita\prefiller\contracts\IMemorableForm               $model
     * @param \insolita\prefiller\PrefillConfig $config
     *
     * @return Model|\insolita\prefiller\contracts\IMemorableForm
     */
    public function persist($model, PrefillConfig $config);
}
