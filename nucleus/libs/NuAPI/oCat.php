<?php
/**
 * Description of oCat
 *
 * @author lordmatt
 */
class oCat extends oBlog {
    
    protected $id;
    
    public function __construct($id) {
        $this->id = $id;
        parent::__construct($this->myBlogID($id));
    }
    
    public function catID($id){
        return $id;
    }
    
    protected function myBlogID($cid){
        $res = sql_query('SELECT cblog FROM '.sql_table('category')." WHERE catid='{$cid}'");
        $o = sql_fetch_object($res);
        return (int) $o->cblog;
    }
    
    /**
     * Returns the SQL used to build the list of item IDs.
     * 
     * Would prefer to use MySQLi's OO but NucleusCMS has its own methods.
     * @param string $extraQuery
     * @return string 
     */
    protected function sql_for_lookup($extraQuery=''){
        $extraQuery.= ' AND i.icat='.$this->catID();
        return parent::sql_for_lookup($extraQuery);
    }
    /**
     * Get the parent blog as an object
     * @return oBlog 
     */
    public function &blog(){
        $oFactory = oFactory::Instance();
        return $oFactory->get_oBlog($this->BLOG->getID());
    }
    
    // From oBlog but not used (as such)
    /**
     * NOT RECOMENDED AT ALL!
     * 
     * USE: 
     *   $blog = $oCat->blog();
     *   $aCat = $blog->get_category_by_id($id);
     * 
     * @param int $id
     * @return oCat 
     */
    public function &get_category_by_id($id){
        return $this->blog()->get_category_by_id($id);
    }
    /**
     * Inherited method with no contextual meaning
     * @param type $offset
     * @return FALSE 
     */
    public function &category($offset=0){
        return FALSE;
    }
    /**
     * NOT RECOMENDED AT ALL!
     * 
     * USE: 
     *   $blog = $oCat->blog();
     *   $list = $blog->get_category_list();
     * 
     * @return array 
     */
    public function &get_category_list(){
        return $this->blog()->get_category_list();
    }
    
}