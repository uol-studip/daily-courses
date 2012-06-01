<? $RssTimeFmt = '%Y-%m-%dT%H:%MZ';?>
<?= '<?xml version="1.0"?>'?>

<rss version="2.0" xmlns:studip="http://www.studip.de/">
<channel>
      <title><?=htmlspecialchars(studip_utf8encode($title))?></title>
      <link><?=htmlspecialchars(UrlHelper::getUrl($GLOBALS['ABSOLUTE_URI_STUDIP'].'plugins.php/dailycourseslistplugin/show'))?></link>
      <description><?=htmlspecialchars(studip_utf8encode($description))?></description>
      <pubDate><?=gmstrftime($RssTimeFmt , $date)?></pubDate>
      <lastBuildDate><?=gmstrftime($RssTimeFmt )?></lastBuildDate>
      <generator><?=htmlspecialchars(studip_utf8encode('Stud.IP - ' . $GLOBALS['SOFTWARE_VERSION']))?></generator>
<? foreach ($data as $s) : ?>
      <item>
         <title><?=htmlspecialchars(strftime("%R",$s['date']) . ' - ' . strftime("%R",$s['end_time']) . ' ' . studip_utf8encode($s['name']))?></title>
         <link><?=htmlspecialchars(UrlHelper::getUrl($GLOBALS['ABSOLUTE_URI_STUDIP'].'details.php', array('cid' => null, 'sem_id' => $s['seminar_id'])))?></link>
<?
         $desc = 'Zeit: ' . strftime("%R",$s['date']) . ' - ' . strftime("%R",$s['end_time']) . '<br>';
         $desc .= 'Raum: ' . htmlspecialchars(studip_utf8encode($s['raum'])) . '<br>';
         $desc .= 'Dozenten: ' . htmlspecialchars(studip_utf8encode($s['dozenten_termin'] ? $s['dozenten_termin'] : $s['dozenten']));
?>
         <description><![CDATA[<?=$desc?>]]></description>
         <pubDate><?=gmstrftime($RssTimeFmt ,$s['date'])?></pubDate>
         <guid><?=$s['termin_id']?></guid>
         <studip:course><?=htmlspecialchars(studip_utf8encode($s['name']))?></studip:course>
         <studip:start_time><?=$s['date']?></studip:start_time>
         <studip:end_time><?=$s['end_time']?></studip:end_time>
         <studip:room><?=htmlspecialchars(studip_utf8encode($s['raum']))?></studip:room>
         <studip:lecturer><?=htmlspecialchars(studip_utf8encode($s['dozenten_termin'] ? $s['dozenten_termin'] : $s['dozenten']))?></studip:lecturer>
      </item>
<? endforeach; ?>
</channel>
</rss>

