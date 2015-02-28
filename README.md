# YouTube Audio Player Generation Library

[![Build Status](https://scrutinizer-ci.com/g/navarr/ytaudio/badges/build.png?b=master)](https://scrutinizer-ci.com/g/navarr/ytaudio/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/navarr/ytaudio/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/navarr/ytaudio/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/navarr/ytaudio/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/navarr/ytaudio/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/navarr/youtube/v/stable.svg)](https://packagist.org/packages/navarr/youtube) [![Total Downloads](https://poser.pugx.org/navarr/youtube/downloads.svg)](https://packagist.org/packages/navarr/youtube) [![Latest Unstable Version](https://poser.pugx.org/navarr/youtube/v/unstable.svg)](https://packagist.org/packages/navarr/youtube) [![License](https://poser.pugx.org/navarr/youtube/license.svg)](https://packagist.org/packages/navarr/youtube)

YTAudio is a helper designed to help generate a "YouTube audio embeddable" - a version of the YouTube flash player that 
is small enough that only the audio controls show.

YTAudio does not extract the audio from YouTube videos, nor does it eliminate the overhead of processing YouTube videos
when playing the files.

Most of the class allows daisy-chaining, since it is primarily controlled through setters and is intended as a helper
class.  Below are two examples of rendering a YTAudio object.

    use Navarr\YouTube\AudioPlayer;

    AudioPlayer::create('http://www.youtube.com/watch?v=dvgZkm1xWPE&ob=av2n')
        ->size(AudioPlayer::SIZE_LARGE)
        ->theme(AudioPlayer::THEME_DARK)
        ->hd() // Force HD
        ->loop() // Loop
        ->progressBar() // Show Progress Bar
        ->timeCode() // Show Time Code
        ->autoplay() // Autoplay
        ->render(); // Output XHTML

This method uses daisy-chaining for setting.  Any daisy-chain function that turns a feature on or off takes a boolean,
with the default value being true.

    use Navarr\YouTube\AudioPlayer;

    AudioPlayer::create(
        'http://www.youtube.com/watch?v=dvgZkm1xWPE&ob=av2n',
        [
            'size' => AudioPlayer::SIZE_LARGE,
            'theme' => AudioPlayer::THEME_DARK,
            'hd',
            'loop',
            'autoplay',
            'progressbar',
            'timecode',
        ]
    )->render();

This method accepts the configuration as an array.  You can daisy chain after it, but you probably will not need to.
