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
 * A generic interface for handling data bound objects
 * @author lordmatt
 */
interface handler {
    
    /*
     * General inspection methods
     */
    
    public function get_vars();
    public function get_val($var);
    public function get_original_val($var);
    //public function insepect_var($var);
    public function is_writable($var);
    
    /*
     * Updating values to the DB (if not protected)
     */
    
    public function update_var($var,$val);
    public function commit_updates();
    
    /*
     * Methods to prevent updates to some or all vars
     */
    
    public function protect_var($var);
    public function lock_object();
    
    public function &request_library_object();
}

