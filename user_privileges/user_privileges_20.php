<?php


//This is the access privilege file
$is_admin=false;

$current_user_roles='H14';

$current_user_parent_role_seq='H1::H3::H11::H14';

$current_user_profiles=array(13,);

$profileGlobalPermission=array('1'=>1,'2'=>1,);

$profileTabsPermission=array('1'=>0,'2'=>1,'4'=>0,'6'=>0,'7'=>1,'8'=>0,'9'=>0,'10'=>0,'13'=>0,'14'=>1,'15'=>0,'16'=>0,'18'=>1,'19'=>1,'20'=>1,'21'=>1,'22'=>1,'23'=>1,'24'=>0,'25'=>1,'26'=>0,'31'=>0,'34'=>1,'35'=>1,'37'=>1,'39'=>0,'41'=>0,'42'=>0,'43'=>0,'44'=>0,'45'=>0,'46'=>0,'47'=>1,'48'=>1,'49'=>0,'50'=>1,'51'=>1,'52'=>1,'53'=>1,'54'=>0,'55'=>1,'56'=>1,'57'=>1,'58'=>0,'59'=>0,'60'=>0,'61'=>0,'62'=>0,'63'=>0,'64'=>0,'65'=>0,'66'=>0,'67'=>0,'68'=>0,'69'=>0,'70'=>0,'28'=>0,'3'=>0,);

$profileActionPermission=array(2=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,10=>0,),4=>array(0=>1,1=>1,2=>1,4=>0,5=>1,6=>1,8=>1,10=>1,),6=>array(0=>1,1=>1,2=>1,4=>0,5=>1,6=>1,8=>1,10=>1,),7=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,8=>0,9=>0,10=>0,),8=>array(0=>0,1=>0,2=>0,4=>0,6=>0,),9=>array(0=>0,1=>0,2=>0,4=>0,5=>0,6=>0,),13=>array(0=>0,1=>0,2=>0,4=>0,5=>0,6=>0,8=>0,10=>0,),14=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,10=>0,),15=>array(0=>0,1=>0,2=>0,4=>0,),16=>array(0=>0,1=>0,2=>0,4=>0,5=>0,6=>0,),18=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,10=>0,),20=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,),21=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,),22=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,),23=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,),26=>array(0=>1,1=>1,2=>1,4=>0,),34=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,10=>0,),35=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,10=>0,),41=>array(0=>0,1=>0,2=>0,4=>0,),42=>array(0=>0,1=>0,2=>0,4=>0,5=>0,6=>0,10=>0,),43=>array(0=>0,1=>0,2=>0,4=>0,5=>0,6=>0,10=>0,),44=>array(0=>0,1=>0,2=>0,4=>0,5=>0,6=>0,10=>0,),46=>array(0=>0,1=>0,2=>0,4=>0,),50=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,8=>0,),51=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,8=>0,),52=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,8=>0,),53=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,8=>0,),54=>array(0=>0,1=>0,2=>0,4=>0,5=>0,6=>0,8=>0,),55=>array(0=>1,1=>1,2=>1,4=>1,5=>0,6=>0,8=>0,),58=>array(0=>0,1=>0,2=>0,4=>0,),59=>array(0=>0,1=>0,2=>0,4=>0,),60=>array(0=>0,1=>0,2=>0,4=>0,),61=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>1,6=>1,8=>1,),62=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>1,6=>1,8=>1,),63=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>1,6=>1,8=>1,),64=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>1,6=>1,8=>1,),65=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>1,6=>1,8=>1,),66=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>1,6=>1,8=>1,),70=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>1,6=>1,8=>1,),);

$current_user_groups=array(3,7,);

$subordinate_roles=array();

$parent_roles=array('H1','H3','H11',);

$subordinate_roles_users=array();

$user_info=array('user_name'=>'Manon','is_admin'=>'off','user_password'=>'$1$Ma000000$L.eTpTkOEnI/Pqpae6hbY0','confirm_password'=>'$1$Ma000000$L.eTpTkOEnI/Pqpae6hbY0','first_name'=>'Manon','last_name'=>'D','roleid'=>'H14','email1'=>'manon.dalban@sortirdunucleaire.fr','status'=>'Inactive','activity_view'=>'This Month','lead_view'=>'Today','hour_format'=>'24','end_hour'=>'','start_hour'=>'07:00','title'=>'','phone_work'=>'','department'=>'','phone_mobile'=>'','reports_to_id'=>'','phone_other'=>'','email2'=>'','phone_fax'=>'','secondaryemail'=>'','phone_home'=>'','date_format'=>'dd-mm-yyyy','signature'=>'','description'=>'','address_street'=>'','address_city'=>'','address_state'=>'','address_postalcode'=>'','address_country'=>'','accesskey'=>'cIU7WVKIRgONbz0E','time_zone'=>'Europe/Brussels','currency_id'=>'1','currency_grouping_pattern'=>'123,456,789','currency_decimal_separator'=>',','currency_grouping_separator'=>'&#039;','currency_symbol_placement'=>'1.0$','imagename'=>'','internal_mailer'=>'0','theme'=>'woodspice','language'=>'fr_fr','reminder_interval'=>'1 Day','no_of_currency_decimals'=>'2','truncate_trailing_zeros'=>'0','dayoftheweek'=>'Monday','callduration'=>'30','othereventduration'=>'120','calendarsharedtype'=>'public','default_record_view'=>'Summary','leftpanelhide'=>'1','rowheight'=>'medium','ccurrency_name'=>'','currency_code'=>'EUR','currency_symbol'=>'&#8364;','conv_rate'=>'1.00000','record_id'=>'','record_module'=>'','currency_name'=>'Euro','id'=>'20');
?>