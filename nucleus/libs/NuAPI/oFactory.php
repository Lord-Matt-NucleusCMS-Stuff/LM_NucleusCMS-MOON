<?php
/*
 * MOON: Matthew's Object Oriented NucleusCMS
 * Copyright (C) 2014-2015 Matthew Brown
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * (see nucleus/documentation/index.html#license for more info)
 */
/**
 * oFactory provides a factory class to access all MOON objects
 *
 * @author lordmatt
 */
final class oFactory {
    //put your code here
    
    protected $cache;
    
    /**
     * Call this method to get oFactory
     *
     * @return oFactory
     */
    public static function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new oFactory();
        }
        return $inst;
    }

    /**
     * Private ctor so nobody else can instance it
     */
    private function __construct() { }
    
    protected function &get_make_o($what,$id){
        $chName = "{$what}s";
        $OBJ = 'o'.ucfirst($what);
        if(isset($this->cache[$chName][$id]) && is_object($this->cache[$chName][$id])){
            return $this->cache[$chName][$id];
        }
        $this->cache[$chName][$id] = new $OBJ($id);
        return $this->cache[$chName][$id];
    }
    /**
     *
     * @param int $id
     * @return oItem 
     */
    public function &get_oItem($id){
        /*
        if(isset($this->cache['items'][$id]) && is_object($this->cache['items'][$id])){
            return $this->cache['items'][$id];
        }
        $this->cache['items'][$id] = new oItem($id);
        return $this->cache['items'][$id];
         * 
         */
        return $this->get_make_o('item', $id);
    }
    
    /**
     *
     * @param int $id
     * @return oBlog 
     */
    public function &get_oBlog($id){
        return $this->get_make_o('blog', $id);
    }
    /**
     *
     * @param int $id
     * @return oCat 
     */
    public function &get_oCat($id){
        return $this->get_make_o('cat', $id);
    }
    
}

