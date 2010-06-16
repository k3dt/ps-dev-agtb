<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/SugarView.php');

class ViewModulelistmenu extends SugarView
{
 	public function __construct()
 	{
 		$this->options['show_title'] = false;
		$this->options['show_header'] = false;
		$this->options['show_footer'] = false; 	  
		$this->options['show_javascript'] = false; 
		$this->options['show_subpanels'] = false; 
		$this->options['show_search'] = false; 
 		parent::SugarView();
 	}	
 	
 	public function display()
 	{
 	    //favorites
        $favorites = array();
        foreach ( SugarFavorites::getUserFavoritesByModule($this->module) as $recordFocus )
            $favorites[] = array(
                "record_id" => $recordFocus->record_id,
                "record_name" => $recordFocus->record_name,
                "module" => $recordFocus->module,
                );
        $this->ss->assign('FAVORITES',$favorites);
        //last viewed
        $tracker = new Tracker();
        $history = $tracker->get_recently_viewed($GLOBALS['current_user']->id,$this->module);
        foreach ( $history as $key => $row ) {
            $history[$key]['item_summary_short'] = getTrackerSubstring($row['item_summary']);
            $history[$key]['image'] = SugarThemeRegistry::current()
                ->getImage($row['module_name'],'border="0" align="absmiddle" alt="'.$row['item_summary'].'"');
        }
        $this->ss->assign('LAST_VIEWED',$history);
 	    
 		$this->ss->display('include/MVC/View/tpls/modulelistmenu.tpl');
 	}
}
?>
