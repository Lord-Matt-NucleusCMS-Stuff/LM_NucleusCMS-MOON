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
 * Description of abs_handler
 *
 * @author lordmatt
 */
abstract class abs_handler implements handler {
    
    /**
     * value = $val
     * write = 0|1
     * original = $original_val
     * 
     * @var array 
     */
    protected $data = array();
    
    public function __get($name) {
        return $this->get_val($name);
    }    
    public function __set($name, $value) {
        return $this->update_var($name, $value);
    }
    
    public function __isset($name) {
        if(isset($this->data[$name]['value'])){
            return TRUE;
        }
        $str = "data_get_{$name}";
        if(method_exists($this, $str)){
            return TRUE;
        }
        return false;
    }
    
    
    
    public function is_writable($var){
        $str = "data_is_writable_{$var}";
        if( !isset($this->data[$var]['write'])){
            if( !method_exists($this,$str)){
                if(isset($this->data[$var])){
                    $this->data[$var]['write']=1; //assume true
                    return TRUE;
                }
                return false; // cannot find it, give up, assume false
            }
            $answer = call_user_func(array($this,$str));
            $this->data[$var]['write'] = ($answer == TRUE ? 1 : 0);
            return $answer;
        }else{
            return ($this->data[$var]['write'] == 1 ? TRUE : false);
        }
    }
    
    
    /*
     * General inspection methods
     */
    
    public function get_vars(){
        $str = "data_all";
        $vars = array();
        $vars = array_keys($vars);
        if(method_exists($this, $str)){
            $also = call_user_func(array($this,$str));
            $vars = array_merge($vars,$also);
        }
        return $vars;
    }
    public function get_val($var){
        $str = "data_get_{$var}";
        if(isset($this->data[$var]['value'])){
            return $this->data[$var]['value'];
        }
        if(method_exists($this, $str)){
            $answer = call_user_func(array($this,$str));
            return $answer;
        }
        return false;
    }
    
    /**
     * Gets the value of $var but ignores any uncommited changes 
     */
    public function get_original_val($var){
        if(isset($this->data[$var]['original'])){
            return $this->data[$var]['original'];
        }
        $str = "data_get_original_{$var}";
        if(method_exists($this, $str)){
            $this->data[$var]['original'] = call_user_func(array($this,$str));
            return $this->data[$var]['original'];
        }
        return $this->get_val($var);
    }
    
    /*
     * Updating values to the DB (if not protected)
     */
    
    /**
     * Update an object value
     * @param string $var
     * @param mixed $val
     * @return boolean 
     */
    public function update_var($var,$val){
        if($this->is_writable($var) == false){
            return false; // not writeable, fail
        }
        $str = "data_set_{$var}";
        if( !isset($this->data[$var]['value'])){
            if( !method_exists($this,$str)){
                return false; // cannot find it, fail
            }
            $answer = call_user_func(array($this,$str),$val);
            return $answer;
        }else{
            if(!isset($this->data[$var]['original'])){
                $this->data[$var]['original']=$this->data[$var]['value'];
            }
            $this->data[$var]['value'] = $val;
            return TRUE;
        }
    }
    //public function commit_updates();
    
    /*
     * Methods to prevent updates to some or all vars
     */
    
    public function protect_var($var){
        $this->data[$var]['write'] = 0;
    }
    
    public function lock_object(){
        $all = $this->get_vars();
        foreach($all as $one){
            $this->protect_var($one);
        }
    }
}
