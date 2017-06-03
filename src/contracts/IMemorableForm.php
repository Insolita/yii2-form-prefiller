<?php
/**
 * Created by solly [02.06.17 10:34]
 */

namespace insolita\prefiller\contracts;

/**
 * Interface IMemorableForm
 *
 * @package insolita\prefiller\contracts
 */
interface IMemorableForm
{
    /**
     * @return string|null
     */
    public function formName();
    
    /**
     * @return array
     */
    public function getAttributes();
    
    /**
     * @param array|null $attributeNames
     * @return bool
     */
    public function validate($attributeNames = null);
    
    /**
     * @return void
     */
    public function clearErrors($attribute);
}
