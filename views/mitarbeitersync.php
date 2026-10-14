<?php
	$includesArray = array(
		'title' => 'Mitarbeitersync',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'primevue3' => true,
		'navigationcomponent' => true,
		'filtercomponent' => true,
		'tabulator5' => true,
		//'customJSs' => array('vendor/vuejs/vuedatepicker_js/vue-datepicker.iife.js'),
		'customJSModules' => array(
			'public/extensions/FHC-Core-DasZeit/js/apps/Mitarbeitersync.js'
		),
		'customCSSs' => array(
			'public/css/components/searchbar/searchbar.css'
			//'vendor/vuejs/vuedatepicker_css/main.css'
		)
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="main">

		<!-- Navigation component -->
		<core-navigation-cmpt></core-navigation-cmpt>

		<!-- Mitarbeiter Sync component -->
		<mitarbeitersync></mitarbeitersync>

	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
