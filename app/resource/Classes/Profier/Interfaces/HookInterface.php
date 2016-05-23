<?php
namespace Pentagonal\Profier\Interfaces;

interface HookInterface
{
    /**
     * Add Hooks Function
     *
     * @param string    $hookName            Hook Name
     * @param string    $function_to_replace Function to replace
     * @param Callable  $callable            Callable
     * @param integer   $priority            priority
     * @param integer   $accepted_args       num count of accepted args / parameter
     * @param boolean   $append              true if want to create new / append if not exists
     */
    public function add($hookName, $callable, $priority = 10, $accepted_args = 1, $append = true);
    /**
     * Appending Hooks Function
     *
     * @param  string    $hookName            Hook Name
     * @param  string    $function_to_replace Function to replace
     * @param  Callable  $callable            Callable
     * @param  integer   $priority            priority
     * @param  integer   $accepted_args       num count of accepted args / parameter
     * @param  boolean   $create              true if want to create new if not exists
     */
    public function append($hookName, $callable, $priority = 10, $accepted_args = 1, $create = true);
    /**
     * Check if hook name exists
     *
     * @param  string $hookName              Hook name
     * @param  string $function_to_check     Specially Functions on Hook
     * @return boolean                       true if has hook
     */
    public function exists($hookName, $function_to_check = false);
    /**
     * Applying Hooks for replaceable and returning as $value param
     *
     * @param  string $hookName Hook Name replaceable
     * @param  mixed $value     returning value
     */
    public function apply($hookName, $value);
    /**
     * Call hook now
     *
     * @param  string $hookName Hook Name
     * @param  string $arg      the arguments for next parameter
     */
    public function call($hookName, $arg = '');
    /**
     * Replace Hooks Function
     *
     * @param  string    $hookName            Hook Name
     * @param  string    $function_to_replace Function to replace
     * @param  Callable  $callable            Callable
     * @param  integer   $priority            priority
     * @param  integer   $accepted_args       num count of accepted args / parameter
     * @param  boolean   $create              true if want to create new if not exists
     */
    public function replace($hookName, $function_to_replace, $callable, $priority = 10, $accepted_args = 1, $create = true);
    /**
     * Removing Hooks
     *
     * @param  string  $hookName           Hook Name
     * @param  string  $function_to_remove functions that to remove from determine $hookName
     * @param  integer $priority           priority
     * @return boolean                     true if has removed
     */
    public function remove($hookName, $function_to_remove, $priority = 10);
    /**
     * Remove all of the hooks from a filter.
     *
     * @param string   $hookName    The filter to remove hooks from.
     * @param int|bool $priority    Optional. The priority number to remove. Default false.
     * @return true                 True when finished.
     */
    public function removeAll($hookName, $priority = false);
    /**
     * Current position
     * @return string functions
     */
    public function current();
    /**
     * Count all existences Hook
     *
     * @param  string $hookName Hook name
     * @return integer          Hooks Count
     */
    public function count($hookName);
    /**
     * Check if hook has doing
     *
     * @param  string $hookName Hook name
     * @return boolean           true if has doing
     */
    public function isDo($hookName = null);
    /**
     * Check if action hook as execute
     *
     * @param  string $hookName Hook Name
     * @return integer          Count of hook action if has did action
     */
    public function isCalled($hookName);
}
