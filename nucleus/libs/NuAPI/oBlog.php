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
 * An object reprisenting a specific blog
 * Where you look for the collection of items and also other blog values
 *
 * @author lordmatt
 */
class oBlog extends abs_handler implements handler {
    protected $BLOG;
    protected $items;
    protected $item_list;
    
    // sub set limits
    protected $ss_from, $ss_to, $ss_member;
    protected $ss_drafts =true;
    protected $ss_future =true;
    protected $subsets=array();
    
    public function __construct($id) {
        if(BLOG::existsID($id)){
            $MANAGER = MANAGER::instance();
            $this->BLOG = $MANAGER->getBlog($id);
        }else{
            user_error("Blog {$id} does not exist");
            unset($this);
        }
        $this->cache_values();
    }
    
    public function commit_updates() {
        $vars = $this->get_vars();
        foreach($vars as $var){
            $this->BLOG->setSetting($var,$this->get_val($var));
        }
        $this->BLOG->writeSettings();
    }

    public function &request_library_object(){
        return $this->BLOG;
    }

    
    public function &createNewCategory($catName = '', $catDescription = _CREATED_NEW_CATEGORY_DESC) {
        $id = $this->BLOG->createNewCategory($catName, $catDescription);
        if($id==0){
            return FALSE;
        }
        $oFactory = oFactory::Instance();
        $cat = $oFactory->get_oCat($id);
        return $cat;
    }
    
    public function &get_category_by_id(){
        $oFactory = oFactory::Instance();
    }
    
    public function &category($offset=0){
        $oFactory = oFactory::Instance();
    }
    
    public function &get_category_list(){
        
    }
    
    
    /**
     * Pulls out all the values of BLOG as a bound data object and adds them to 
     * $this->data 
     */
    protected function cache_values(){
        // is there a better way to do this?
        foreach($this->BLOG->settings as $what=>$setting){
            $this->update_var($what,$setting);
        }
    }
    
    
    
    // new OO
    /**
     * Checks if the item ID belongs to this blog
     * @param int $id
     * @return boolean 
     */
    public function item_is_from_blog($id){
        $list = $this->get_item_list();
        if(in_array($id, $list)){
            return true;
        }
        return false;
    }
    /**
     *
     * @param int $offset
     * @return boolean 
     */
    public function item_exists($offset){
        $this->get_item_list();
        return isset($this->items[$offset]);
    }
    /**
     *
     * @param int $offset
     * @return oItem 
     */
    public function &item($offset=0){
        if($this->item_exists($offset)){
            return $this->item_by_id($this->items[$offset]);
        }
        return FALSE;
    }
    
    /**
     * returns an object for handling the item but only if it belongs to the 
     * blog
     * @param int $id
     * @return oItem 
     */
    public function &item_by_id($id){
        if(isset($this->item[$id]) && is_object($this->item[$id])){
            return $this->item[$id];
        }
        if( !$this->item_is_from_blog($id)){
            return false;
        }
        $oFactory = oFactory::Instance();
        $this->item[$id] = $oFactory->get_oItem($id);
        return $this->item[$id];
    }
    
    public function get_item_list(){
        if(is_array($this->item_list) && count($this->item_list)>0){
            return $this->item_list;
        }
        $this->sql_item_lookup();
        return $this->item_list;
    }
    
    protected function sql_item_lookup(){
        $res=sql_query($this->sql_for_lookup());
        $this->item_list = array();
        if(mysql_num_rows($res)>0){
            while($a = sql_fetch_array($res)) {
                $this->item_list[] = $a[0];
            }            
        }
    }
    
    protected function subset_sql_use($name){
        $name = "ss_{$name}";
        if(isset($this->$name) && trim($this->$name)!=""){
            return TRUE;
        }
        return FALSE;
    }
    
    protected function subset_sql(){
        $SQL = '';
        if($this->subset_sql_use('from')){
            $SQL .= " AND i.itime >= ".mysqldate($this->ss_from);
        }
        if($this->subset_sql_use('to')){
            $SQL .= " AND i.itime =< ".mysqldate($this->ss_to);
        }
        if($this->subset_sql_use('member')){
            $SQL .= " AND i.iauthor = ".mysqldate($this->ss_member);
        }
        if (!$this->ss_drafts) $SQL .= ' and i.idraft=0';	// exclude drafts						
	if (!$this->ss_future) $SQL .= ' and i.itime<=' . mysqldate($this->BLOG->getCorrectTime()); // don't show future items

        return $SQL;
    }
    
    protected function cast_to_ar($what){
        if($what===null){
            return 'null';
        }
        if($what===true){
            return 'true';
        }
        if($what===false){
            return 'false';
        }
        if(trim($what)==''){
            return 'n-a';
        }
        return $what;
    }
    
    public function subset($from=null,$to=null,$member=null,$drafts=true,$future=true){
        $this->ss_from      = $from;
        $this->ss_to        = $to;
        $this->ss_member    = $member;
        $this->ss_drafts    = $drafts;
        $this->ss_future    = $future;
        $this->sql_item_lookup();
    }
    
    public function &get_subset($from,$to=null,$member=null,$drafts=true,$future=true){
        //$oFactory = oFactory::Instance();
        $a=$this->cast_to_ar($from);
        $b=$this->cast_to_ar($to);
        $c=$this->cast_to_ar($member);
        $d=$this->cast_to_ar($drafts);
        $e=$this->cast_to_ar($future);
        if(isset($this->subsets[$a][$b][$c][$d][$e]) && is_object($this->subsets[$a][$b][$c][$d][$e])){
            return $this->subsets[$a][$b][$c][$d][$e];
        }
        $clone = clone $this;
        $this->subsets[$a][$b][$c][$d][$e] &= $clone;
        $clone->subset($from, $to, $member, $drafts, $future);
        return $clone;
    }

    /**
     * Would prefer to use MySQLi but NucleusCMS has its own methods
     * @param string $extraQuery
     * @return string 
     */
    protected function sql_for_lookup($extraQuery=''){
        $query = 'SELECT i.inumber as itemid';
        $query .= ' FROM `'.sql_table('item').'` as i'
                . ' WHERE i.iblog='.$this->getID();

        //. ' and i.iauthor=m.mnumber'
        //. ' and i.icat=c.catid'
        //. ' and i.idraft=0'	// exclude drafts
        // don't show future items
        //. ' and i.itime<=' . mysqldate($this->getCorrectTime());
        
        $query .= $extraQuery;
        $query .= $this->subset_sql();
        $query .= ' ORDER BY i.itime DESC';

        return $query;
    }
    
    // Simple Pass throughs

    /**
     * Tries to add a member to the team. 
     * Returns false if the member was already on the team
     *
     * @param int $memberid
     * @param int $admin
     * @return bool 
     */
    public function addTeamMember($memberid, $admin){
        $result = $this->BLOG->addTeamMember($memberid, $admin);
        return ($result ? TRUE : FALSE); // return val is now === bool
    }
    
    /**
     * provides the UID int of the blog object
     * @return int 
     */
    public function getID(){
        return $this->BLOG->getID();
    }
    
    
}

