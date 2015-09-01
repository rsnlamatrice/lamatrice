

/* ED150830
 * Création du champ ref4d qui contient la valeur de contact_no sans le préfixe
 
 cf modules\RSNImportSources\helpers\Utils.php  METHODES POUR POST-PREIMPORT 
 
 index et triggers devraient être supprimés après la migration
 
 */

ALTER TABLE `vtiger_contactdetails` ADD `ref4d` INT(11) NOT NULL ;

UPDATE `vtiger_contactdetails` SET `ref4d`= CAST(SUBSTR(`contact_no`, 2) AS UNSIGNED);

ALTER TABLE  `vtiger_contactdetails` ADD INDEX (  `ref4d` );

CREATE TRIGGER `TRG_CONTACTDETAILS_BFUPDATE` BEFORE UPDATE ON `vtiger_contactdetails` FOR EACH ROW SET NEW.ref4d = SUBSTR(NEW.contact_no, 2);
CREATE TRIGGER `TRG_CONTACTDETAILS_BFINSERT` BEFORE INSERT ON `vtiger_contactdetails` FOR EACH ROW SET NEW.ref4d = SUBSTR(NEW.contact_no, 2);


/* ED150830
 * champ et triggers devraient être supprimés après la migration
 
 */


DROP TRIGGER `TRG_CONTACTDETAILS_BFUPDATE`;
DROP TRIGGER `TRG_CONTACTDETAILS_BFINSERT`;

ALTER TABLE  `vtiger_contactdetails` DROP COLUMN (  `ref4d` );

