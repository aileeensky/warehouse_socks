<?php

if (!function_exists('set_active')) {
    /**
     * Menambahkan class active jika URL cocok
     * 
     * @param string $uri Uri yang ingin dicek
     * @return string
     */
    function set_active($uri)
    {

        return service('uri')->getPath() === $uri ? 'active' : '';
    }
}
if (!function_exists('set_active_sub')) {
    /**
     * Menambahkan class active jika URL cocok
     * 
     * @param string $uri Uri yang ingin dicek
     * @return string
     */
    function set_active_sub($uri)
    {
        return service('uri')->getPath() === $uri ? 'active' : '';
    }
}
