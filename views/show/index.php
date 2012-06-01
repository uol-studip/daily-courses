<?php echo $this->render_partial('tkit.php')?>
<div style="padding-left:1em;padding-right:1em">
<h2>
<?=sprintf(_("Veranstaltungen am %s"), strftime("%A, %x", $date))?>
<a href="<?=$controller->link_for('show/rss', array('DailyCoursesListPlugin_date' => null))?>"><?=Assets::img('icons/16/red/rss.png', array('align'=>'right', 'title' => _("Feed für tägliche Veranstaltungen")))?></a>
</h2>
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td align="left" width="1%">
<a href="<?=$controller->link_for('show/index?last_day=1')?>">
<?=Assets::img('icons/16/blue/arr_2left.png')?>
</td>
<td align="left" style="padding-left:10px;">
<?=$form->getFormStart($controller->link_for());?>
<table border="0">
<tr>
<td>
<b><?=_("Filtern nach Studiengängen")?>:</b>
</td><td>
<?=$form->getFormField('sem_tree_id')?>
</td>
<td rowspan="3" style="padding-left:5px;">
<?=$form->getFormButton('ok', array('style' => 'vertical-align:middle'))?>
</td>
</tr>
<!--
<tr><td>
<?=_("Veranstaltungstyp")?>:
</td><td>
<?=$form->getFormField('sem_class')?>
</td>
</tr>
-->
<tr><td>
<?=_("Datum")?>:
</td>
<td>
<?=$form->getFormField('starttime');?>
</td>
</tr>
</table>
<?=$form->getFormEnd();?>
</td>
<td align="right" width="1%">
<a href="<?=$controller->link_for('show/index?next_day=1')?>">
<?=Assets::img('icons/16/blue/arr_2right.png')?>
</td>
</tr>
</table>
<hr>
<?if($data){?>
<!--<div><a href="<?=$controller->link_for('show/xls')?>"><?=Assets::img('icons/16/blue/file-xls.png',array('title' => _("Download Excel")))?></a></div>-->
	<table class="sortable" border="0" cellpadding="2" cellspacing="2"
		width="100%">
		<tr style="background: url(<?=Assets::image_path('steelgraudunkel.gif')?>);cursor: pointer;" title="<?=_("Klicken, um die Sortierung zu ändern")?>">
			<th width="5%" class="time sortfirstasc"><?= _("Beginn") ?></th>
			<th width="5%" class="time"><?= _("Ende") ?></th>
			<th width="30%" class="text"><?= _("Name") ?></th>
			<th width="30%" class="text"><?= _("Lehrende") ?></th>
			<th width="30%" class="text"><?= _("Raum") ?></th>
		</tr>

		<? foreach ($data as $s) : ?>
		<tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
			<td style="text-align: center; white-space: nowrap;"><?=strftime("%R",$s['date'])?></td>
			<td style="text-align: center; white-space: nowrap;"><?=strftime("%R",$s['end_time'])?></td>
			<td style="text-align: left; white-space: nowrap;"><a href="<?=UrlHelper::getLink('details.php', array('cid' => null, 'sem_id' => $s['seminar_id']))?>"><?=htmlready(my_substr($s['name'],0 , 100))?> (<?=htmlready(my_substr($s['vak'],0 ,10))?>)</a></td>
			<td style="text-align: left;">
			<?=($s['dozenten_termin'] ? htmlready($s['dozenten_termin']) : htmlready($s['dozenten']))?>
			</td>
			<td style="text-align: left;"><?=htmlready( $s['raum'])?></td>
		</tr>
		<? endforeach ; ?>
	</table>
<?}?>
</div>