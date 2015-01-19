<?php
/**
 * Written by yours truly
 * https://discussions.vtiger.com/index.php?p=/discussion/171186/detail-view-widgets-implementation/p1
 *
 * see also /vtiger/models/DetailVew.php after $moduleModel = $this->getModule() in the getWidgets() function
 */

class cWidget{
    public $widgets = array();
    
    public function GetDetailViewWidget($tabid, $url_extension){
        global $adb;
        $widgets = array();
        $query = "SELECT * FROM vtiger_links WHERE tabid = ? 
                  AND linktype = ?
                  AND linklabel != ?
                  ORDER BY sequence";
        
        $result = $adb->pquery($query, array($tabid, "DETAILVIEWWIDGET", "DetailViewBlockCommentWidget"));
        if($adb->num_rows($result) > 0){
            foreach($result AS $k => $v){
                $widgets[] = array(
                                'linktype' => 'DETAILVIEWWIDGET',
                                'linklabel' => $v['linklabel'],
                                'linkurl' => $v['linkurl'] . $url_extension);
            }
        }
        $this->widgets = $widgets;
        return $widgets;
    }
}

?>