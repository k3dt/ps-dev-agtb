<?php
require_once('include/SugarCache/SugarCacheAbstract.php');

class SugarCacheAPC extends SugarCacheAbstract
{
    /**
     * @see SugarCacheAbstract::$_priority
     */
    protected $_priority = 940;
    
    /**
     * @see SugarCacheAbstract::useBackend()
     */
    public function useBackend()
    {
        if ( !parent::useBackend() )
            return false;
        
        if ( function_exists("apc_store")
                && empty($GLOBALS['sugar_config']['external_cache_disabled_apc']))
            return true;
            
        return false;
    }
    
    /**
     * @see SugarCacheAbstract::_setExternal()
     */
    protected function _setExternal(
        $key,
        $value
        )
    {
        apc_store($key,$value,$this->expireTimeout);
    }
    
    /**
     * @see SugarCacheAbstract::_getExternal()
     */
    protected function _getExternal(
        $key
        )
    {
        if ( apc_fetch($key) === false ) {
            return null;
        }
        
        return apc_fetch($key);
    }
    
    /**
     * @see SugarCacheAbstract::_clearExternal()
     */
    protected function _clearExternal(
        $key
        )
    {
        apc_delete($key);
    }
    
    /**
     * @see SugarCacheAbstract::_resetExternal()
     */
    protected function _resetExternal()
    {
        apc_clear_cache('user');
    }
}
