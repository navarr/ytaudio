<?php

use Navarr\YouTube\AudioPlayer;
use Navarr\YouTube\AudioPlayerException;

class AudioPlayerTest extends PHPUnit_Framework_TestCase
{
    const YOUTUBE_VIDEO = 'https://www.youtube.com/watch?v=jNQXAC9IVRw';

    public function testCanSetHttps()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->https(false);
        $this->assertFalse($player->isHTTPS());
        $this->assertFalse($player->getHTTPS());
        $this->assertTrue($player->isHTTP());

        $player->https();
        $this->assertTrue($player->isHTTPS());
        $this->assertTrue($player->getHTTPS());
        $this->assertFalse($player->isHTTP());
    }

    public function testCanSetCookieUse()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->cookies(false);
        $this->assertFalse($player->getCookies());
        $this->assertFalse($player->willUseCookies());

        $player->cookies();
        $this->assertTrue($player->getCookies());
        $this->assertTrue($player->willUseCookies());
    }

    public function testCanSetTimeCode()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->timeCode(false);
        $this->assertFalse($player->getTimeCode());
        $this->assertFalse($player->hasTimeCode());

        $player->timeCode(true);
        $this->assertTrue($player->getTimeCode());
        $this->assertTrue($player->hasTimeCode());
    }

    public function testCanSetProgressBar()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->progressBar(false);
        $this->assertFalse($player->getProgressBar());
        $this->assertFalse($player->hasProgressBar());

        $player->progressBar(true);
        $this->assertTrue($player->getProgressBar());
        $this->assertTrue($player->hasProgressBar());
    }

    public function testCanSetLoop()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->loop(false);
        $this->assertFalse($player->getLoop());
        $this->assertFalse($player->willLoop());

        $player->loop(true);
        $this->assertTrue($player->getLoop());
        $this->assertTrue($player->willLoop());
    }

    public function testCanSetUsesJavascriptApi()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->jsAPI(false);
        $this->assertFalse($player->getJSAPI());
        $this->assertFalse($player->canUseJSAPI());
        $this->assertFalse($player->canUseJavaScriptAPI());

        $player->jsAPI(true);
        $this->assertTrue($player->getJSAPI());
        $this->assertTrue($player->canUseJSAPI());
        $this->assertTrue($player->canUseJavaScriptAPI());
    }

    public function testCanSetAutoplay()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->autoplay(false);
        $this->assertFalse($player->getAutoplay());
        $this->assertFalse($player->willAutoplay());

        $player->autoplay(true);
        $this->assertTrue($player->getAutoplay());
        $this->assertTrue($player->willAutoplay());
    }

    public function testCanSetHd()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->hd(false);
        $this->assertFalse($player->getHD());
        $this->assertFalse($player->isHD());

        $player->hd(true);
        $this->assertTrue($player->getHD());
        $this->assertTrue($player->isHD());
    }

    public function testCanSetThemeLight()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->theme(AudioPlayer::THEME_LIGHT);

        $this->assertEquals(AudioPlayer::THEME_LIGHT, $player->getTheme());
    }

    public function testCanSetThemeDark()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->theme(AudioPlayer::THEME_DARK);

        $this->assertEquals(AudioPlayer::THEME_DARK, $player->getTheme());
    }

    public function testInvalidThemeThrowsException()
    {
        $this->setExpectedException('Navarr\YouTube\AudioPlayerException');

        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->theme('random value');
    }

    public function testCanSetInvisible()
    {
        $player = new AudioPlayer(self::YOUTUBE_VIDEO);

        $player->invisible();

        $this->assertTrue($player->getInvisible());
        $this->assertTrue($player->isInvisible());
        $this->assertFalse($player->isTiny());
        $this->assertFalse($player->isSmall());
        $this->assertFalse($player->isMedium());
        $this->assertFalse($player->isLarge());

        $this->assertEquals(AudioPlayer::SIZE_INVISIBLE, $player->getSize());
    }
}
