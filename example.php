<?php
	require_once('YTAudio.php');
	
	// http://navarr.me/ytaudio/example.php
	
	YTAudio::create
	(
		'http://www.youtube.com/watch?v=dvgZkm1xWPE&ob=av2n'
		,YTAudio::SIZE_LARGE
		,YTAudio::THEME_DARK
	)
	->hd() // Force HD
	->loop() // Loop
	->progressBar() // Show Progress Bar
	->timeCode() // Show Time Code
	->autoplay() // Autoplay
	->render(); // Output XHTML
