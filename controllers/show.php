<?php
require 'application.php';
require_once 'lib/classes/StudipForm.class.php';
require_once 'lib/classes/StudipSemTree.class.php';
require_once 'lib/classes/SeminarCategories.class.php';

class ShowController extends ApplicationController {

	function before_filter($action, $args){
		parent::before_filter($action, $args);
		foreach(words('sem_tree_id sem_class') as $p){
		    if (Request::get($this->plugin->me . '_' . $p) !== null ) {
			    $this->$p = Request::get($this->plugin->me . '_' . $p);
			    $GLOBALS['user']->user_vars[$this->plugin->me . '_' . $p] = $this->$p;
			} else {
			    $this->$p = $GLOBALS['user']->user_vars[$this->plugin->me . '_' . $p];
			}
			UrlHelper::addLinkParam($this->plugin->me . '_' . $p, $this->$p);
		}
		if(!$this->date) {
			$this->date = strtotime('today 02:01');
		}
		if(!$this->sem_tree_id){
			$this->sem_tree_id = null;
		}
		if(!$this->sem_class){
			$this->sem_class = null;
		}
		UrlHelper::bindLinkParam($this->plugin->me . '_' .'date', $this->date);
	}

	function index_action(){


		if(Request::int('last_day')){
			$this->date = strtotime("-1 day 02:01", $this->date);
		}
		if(Request::int('next_day')){
			$this->date = strtotime("+1 day 02:01", $this->date);
		}
		$tree = TreeAbstract::getInstance('StudipSemTree');
		$tree->buildIndex();
		$sem_tree_options[] = array('name' => _("Alle Veranstaltungen des gewählten Tages"), 'value' => 0, 'attributes' => array('class' => 'top-level'));
		$faecher = array_keys(array_filter($tree->tree_data, function($t) {return $t['type']==3;}));

              //Fakultäten und Lehreinheiten
              foreach($tree->getKids('root') as $item) {
			$sem_tree_options[] = array(
                         'name' => ' Alles aus '.my_substr($tree->tree_data[$item]['name'],0,100),
                         'value' => $item,
                         'attributes' => array('class' => 'top-level'),
                     );

                    // Mit Lehreinheiten (also das erste Kind)
                    /*
                     if($tree->hasKids($item)){
				foreach($tree->getKids($item) as $subitem){
					$sem_tree_options[] = array('name' => my_substr($tree->getShortPath($subitem),0,100), 'value' => $subitem );
				}
			}
                     */
		}

		//nur Fächer
/*
		foreach($faecher as $item) {
			$sem_tree_options[] = array('name' => my_substr($tree->tree_data[$item]['name'],0,100), 'value' => $item );
		}
*/
		//Fächern und erste Ebene unter den Fächern mit >

              foreach($faecher as $item) {
		    if($tree->hasKids($item)){
				foreach($tree->getKids($item) as $subitem){
					$sem_tree_options[] = array('name' => my_substr($tree->tree_data[$item]['name'] .' (' . $tree->tree_data[$subitem]['name'],0,100).')', 'value' => $subitem );
				}
			}
		}
		usort($sem_tree_options, function($a,$b) use ($tree) {return (int)($tree->tree_data[$a["value"]]["index"] - $tree->tree_data[$b["value"]]["index"]);});
		$sem_class_options[] = array('name' => _("Alle"), 'value' => 0);
		foreach(SeminarCategories::getAll() as $sem_class){
		    if (!$sem_class->studygroup_mode) {
		        $sem_class_options[] = array('name' => $sem_class->name, 'value' => $sem_class->id);
		    }
		}
		$form_fields['starttime']  = array('type' => 'date',  'separator' => '&nbsp;', 'default' => 'YYYY-MM-DD', 'date_popup' => true);
              $form_fields['sem_tree_id'] = array('type' => 'select', 'options' => $sem_tree_options);
		$form_fields['sem_class'] = array('type' => 'select', 'options' => $sem_class_options);

		$form_buttons['ok'] = array('type' => 'uebernehmen', 'info' => _("Übernehmen"));

		$form_fields['starttime']['default_value'] = StudipForm::TimestampToSQLDate($this->date);
		$form_fields['sem_tree_id']['default_value'] = $this->sem_tree_id;
		$form_fields['sem_class']['default_value'] = $this->sem_class;


		$form = new StudipForm($form_fields, $form_buttons, $this->plugin->me, false);

		if($form->isClicked('ok')){
			$this->date = StudipForm::SQLDateToTimestamp($form->getFormFieldValue('starttime'));
			$this->sem_tree_id = $form->getFormFieldValue('sem_tree_id');
			$this->sem_class = $form->getFormFieldValue('sem_class');
		}
		$this->form = $form;

		$this->data = $this->get_data();
		if(!count($this->data)) {
			$this->flash_now('info', _("Mit den gewählten Filtern wurden keine Veranstaltungen gefunden."));
		}
	}

	function get_data(){
	    $trp = DBManager::get()->query("SHOW TABLES LIKE 'termin_related_persons'")->fetchColumn();
		$tree = TreeAbstract::getInstance('StudipSemTree');
		$filter = array();
		$filter[] = sprintf("date BETWEEN %s AND %s", strtotime('today 02:01', $this->date), strtotime('today 23:59', $this->date));
		if($this->sem_tree_id){
			$filter[] = "seminar_sem_tree.sem_tree_id IN ('".join("','", array_merge(array($this->sem_tree_id), (array)$tree->getKidsKids($this->sem_tree_id)))."')";
		}
		if($this->sem_class){
			$filter[] = "seminare.status IN(".join(',', array_keys(SeminarCategories::get($this->sem_class)->getTypes())).")";
		}
		$vis_perm = get_config('SEM_VISIBILITY_PERM') ? get_config('SEM_VISIBILITY_PERM') : 'root';
		if(!$GLOBALS['perm']->have_perm($vis_perm)) {
			$filter[] = "seminare.visible=1";
		}
		$ret = array();
		$query = "SELECT SQL_CACHE DISTINCT resources_objects.name as ro_raum,termine.*, seminare.Seminar_id as seminar_id, seminare.Name as name, seminare.VeranstaltungsNummer as vak,
				(SELECT GROUP_CONCAT(".$GLOBALS['_fullname_sql']['no_title_short']." SEPARATOR '; ') FROM seminar_user INNER JOIN auth_user_md5 USING(user_id) WHERE seminar_user.seminar_id=seminare.Seminar_id AND seminar_user.status='dozent' ORDER BY position) as dozenten
				" .
				($trp ? ",(SELECT GROUP_CONCAT(".$GLOBALS['_fullname_sql']['no_title_short']." SEPARATOR '; ') FROM termin_related_persons INNER JOIN auth_user_md5 USING(user_id) WHERE termine.termin_id=termin_related_persons.range_id) as dozenten_termin" : "")
				. " FROM termine
				INNER JOIN seminare ON termine.range_id=seminare.Seminar_id
				LEFT JOIN resources_assign ON assign_user_id=termin_id
				LEFT JOIN resources_objects USING(resource_id)
				LEFT JOIN seminar_sem_tree ON seminar_sem_tree.seminar_id = seminare.Seminar_id
				WHERE " . join(" AND ", $filter) . " ORDER BY termine.date ASC, /*termine.end_time DESC,*/ seminare.Name DESC";
		foreach(DBManager::get()->query($query)->fetchAll(PDO::FETCH_ASSOC) as $one) {
		    if ($one['ro_raum']) {
			    $raum = $one['ro_raum'];
			} else if ($one['raum']) {
			    $raum = $one['raum'];
			} else {
			    $raum = _("k.A.");
			}
			$one['raum'] = $raum;
			$ret[] = $one;
		}
		return $ret;
	}

	function xls_action(){
		require_once "vendor/write_excel/OLEwriter.php";
		require_once "vendor/write_excel/BIFFwriter.php";
		require_once "vendor/write_excel/Worksheet.php";
		require_once "vendor/write_excel/Workbook.php";
		$data = $this->get_data();
		$tmpfile = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid('write_excel',1));
		// Creating a workbook
		$workbook = new Workbook($tmpfile);
		$head_format = $workbook->addformat();
		$head_format->set_size(12);
		$head_format->set_bold();
		$head_format->set_align("left");
		$head_format->set_align("vcenter");

		$head_format_merged = $workbook->addformat();
		$head_format_merged->set_size(12);
		$head_format_merged->set_bold();
		$head_format_merged->set_align("left");
		$head_format_merged->set_align("vcenter");
		$head_format_merged->set_merge();
		$head_format_merged->set_text_wrap();

		$caption_format = $workbook->addformat();
		$caption_format->set_size(10);
		$caption_format->set_align("left");
		$caption_format->set_align("vcenter");
		$caption_format->set_bold();
		//$caption_format->set_text_wrap();

		$data_format = $workbook->addformat();
		$data_format->set_size(10);
		$data_format->set_align("left");
		$data_format->set_align("vcenter");

		$caption_format_merged = $workbook->addformat();
		$caption_format_merged->set_size(10);
		$caption_format_merged->set_merge();
		$caption_format_merged->set_align("left");
		$caption_format_merged->set_align("vcenter");
		$caption_format_merged->set_bold();

		// Creating the first worksheet
		$worksheet1 = $workbook->addworksheet(strftime("%x", $this->date));

		$max_col = 5;

		foreach(range(0,$max_col) as $c) $worksheet1->write_blank(0,$c,$head_format);
		foreach(range(0,$max_col) as $c) $worksheet1->write_blank(1,$c,$head_format);
		foreach(range(0,$max_col) as $c) $worksheet1->set_column(0, $c, 25);

		$worksheet1->set_row(0, 20);
		$worksheet1->write_string(0, 0, sprintf(_("Veranstaltungen am %s"), strftime("%A, %x", $this->date)),$head_format);
		$worksheet1->set_row(1, 20);

		$row = 2;
		$c = 0;

		$worksheet1->write_string($row,$c++, _("Beginn"), $caption_format);
		$worksheet1->write_string($row,$c++, _("Ende"), $caption_format);
		$worksheet1->write_string($row,$c++, _("Name"), $caption_format);
		$worksheet1->write_string($row,$c++, _("Dozierende"), $caption_format);
		$worksheet1->write_string($row,$c++, _("Raum"), $caption_format);

		++$row;
		foreach($data as $semdata){
			$c = 0;
			$worksheet1->write_string($row, $c++, strftime("%R",$semdata['date']), $data_format);
			$worksheet1->write_string($row, $c++, strftime("%R",$semdata['end_time']), $data_format);
			$worksheet1->write_string($row, $c++, $semdata['name'], $data_format);
			$worksheet1->write_string($row, $c++, ($semdata['dozenten_termin'] ? $semdata['dozenten_termin'] : $semdata['dozenten']), $data_format);
			$worksheet1->write_string($row, $c++, $semdata['raum'], $data_format);
			++$row;
		}
		$workbook->close();
		$this->redirect(getDownloadLink(basename($tmpfile), sprintf(_("Veranstaltungen am %s"), strftime("%A, %x", $this->date)).".xls", 4, 'force'));
	}

	function rss_action()
	{
		$this->data = $this->get_data();
		$this->description = sprintf(_("Veranstaltungen am %s"), strftime("%A, %x", $this->date));
		$this->title = sprintf(_("Veranstaltungsliste - %s"), $GLOBALS['UNI_NAME_CLEAN']);
		$this->set_content_type("text/xml; charset=utf-8");
		$this->set_layout(null);
	}
}

