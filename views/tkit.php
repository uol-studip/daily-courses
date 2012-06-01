<script type="text/javascript" src="<?=$controller->url.'javascripts/prototype.js'?>"></script>
<script type="text/javascript" src="<?=$controller->url.'javascripts/tablekit.js'?>"></script>
<script type="text/javascript">
TableKit.options.rowEvenClass = 'cycle_even';
TableKit.options.rowOddClass = 'cycle_odd';
TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('date-de_DE',{
		pattern : /^\d{2}\.\d{2}\.\d{4}/,
		normal : function(v) {
			v = v.strip();
			if(!this.pattern.test(v)) {return 0;}
			var r = v.match(/^(\d{2})\.(\d{2})\.(\d{4})/);
			var yr_num = r[3];
			var mo_num = parseInt(r[2],10)-1;
			var day_num = r[1];
			return new Date(yr_num, mo_num, day_num).valueOf();
		}})
	);
TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('date-en_GB',{
		pattern : /^\d{2}\/\d{2}\/\d{2}/,
		normal : function(v) {
			v = v.strip();
			if(!this.pattern.test(v)) {return 0;}
			var r = v.match(/^(\d{2})\/(\d{2})\/(\d{2})/);
			var yr_num = '20' + r[3];
			var mo_num = parseInt(r[2],10)-1;
			var day_num = r[1];
			return new Date(yr_num, mo_num, day_num).valueOf();
		}})
	);
TableKit.Sortable.addSortType(
		new TableKit.Sortable.Type('project',{
			normal : function(v) {
				v = v.strip().substring(1,-1);
				v = parseInt(v,10);
				return v;
			}})
		);
</script>
<style>
.sortasc {
	background-image: url(<?=Assets::image_path('dreieck_up.png')?>);
	background-repeat:no-repeat;
	background-position:center right;
}
.sortdesc {
	background-image: url(<?=Assets::image_path('dreieck_down.png')?>);
	background-repeat:no-repeat;
	background-position:center right;
}
th {
	background: none repeat scroll 0 0 transparent;
	padding: 2px 15px 2px 15px;
	text-align:center;
}
</style>
