<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

Events::on('pv21_conf_stammdaten', function (&$res) {
	$res()['mitarbeitersync'] = [
		'title' => 'ZeitaufzeichnungSync',
		'component' => absoluteJsImportUrl('public/extensions/FHC-Core-DasZeit/js/components/Mitarbeitersync.js')
	];
});
