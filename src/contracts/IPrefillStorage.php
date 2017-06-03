<?php
/**
 * Created by solly [28.01.17 23:53]
 */

namespace insolita\prefiller\contracts;

/**
 * Interface IPrefillStorage
 *
 * @package insolita\prefiller\contracts
 */
interface IPrefillStorage
{
    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getValue(string $name, $default = null);
    
    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setValue(string $name, $value);
}
