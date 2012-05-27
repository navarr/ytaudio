<?php
	require_once('YTAudio.php');
	
	// http://navarr.me/ytaudio/example.php
	
	YTAudio::create('http://www.youtube.com/watch?v=dvgZkm1xWPE&ob=av2n')
	->size(YTAudio::SIZE_LARGE)
	->theme(YTAudio::THEME_DARK)
	->hd() // Force HD
	->loop() // Loop
	->progressBar() // Show Progress Bar
	->timeCode() // Show Time Code
	->autoplay() // Autoplay
	->render(); // Output XHTML
	
	YTAudio::create('http://www.youtube.com/watch?v=dvgZkm1xWPE&ob=av2n',array(
		'size' => YTAudio::SIZE_LARGE,
		'theme' => YTAudio::THEME_DARK,
		'hd',
		'loop',
		'autoplay',
		'progressbar',
		'timecode',
	))->render();
