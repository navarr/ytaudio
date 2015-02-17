<?php
use Navarr\YouTube\AudioPlayer;

require_once( '../src/AudioPlayer.php' );

// http://navarr.me/ytaudio/example.php

AudioPlayer::create('http://www.youtube.com/watch?v=dvgZkm1xWPE&ob=av2n')
           ->size(AudioPlayer::SIZE_LARGE)
           ->theme(AudioPlayer::THEME_DARK)
           ->hd()// Force HD
           ->loop()// Loop
           ->progressBar()// Show Progress Bar
           ->timeCode()// Show Time Code
           ->autoplay()// Autoplay
           ->render(); // Output XHTML

AudioPlayer::create('http://www.youtube.com/watch?v=dvgZkm1xWPE&ob=av2n', array(
    'size'  => AudioPlayer::SIZE_LARGE,
    'theme' => AudioPlayer::THEME_DARK,
    'hd',
    'loop',
    'autoplay',
    'progressbar',
    'timecode',
))->render();
