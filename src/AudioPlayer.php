<?php

/**
 * @author Navarr Barnier <me@navarr.me>
 * @license MIT
 */
namespace Navarr\YouTube;

class AudioPlayer
{
    const SIZE_INVISIBLE = 0;
    const SIZE_TINY = 1;
    const SIZE_SMALL = 2;
    const SIZE_MEDIUM = 3;
    const SIZE_LARGE = 4;

    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';

    const TYPE_VIDEO = 1;
    const TYPE_PLAYLIST = 2;

    protected $id = null;
    protected $https = true;
    protected $type = self::TYPE_VIDEO;
    protected $size = self::SIZE_SMALL;
    protected $source = null;
    protected $hd = false;
    protected $autoplay = false;
    protected $jsapi = false;
    protected $progressbar = false;
    protected $timecode = false;
    protected $cookies = true;
    protected $theme = self::THEME_DARK;
    protected $loop = false;

    /**
     * Constructor.
     *
     *
     * @param string $source
     * @param array  $settings
     *
     * @throws AudioPlayerException
     */
    public function __construct($source, $settings = null)
    {
        $this->source($source);

        if ($settings === null) {
            $settings = [];
        }

        // Feature array, ie: array('https','hd','autoplay')
        if (in_array('https', $settings)) {
            $this->https();
        }
        if (in_array('hd', $settings)) {
            $this->hd();
        }
        if (in_array('autoplay', $settings)) {
            $this->autoplay();
        }
        if (in_array('jsapi', $settings)) {
            $this->jsAPI();
        }
        if (in_array('progressbar', $settings)) {
            $this->progressBar();
        }
        if (in_array('timecode', $settings)) {
            $this->timeCode();
        }
        if (in_array('cookies', $settings)) {
            $this->cookies();
        }
        if (in_array('loop', $settings)) {
            $this->loop();
        }

        // Associative Feature Array, ie: array('https' => true, 'hd' => false)
        if (isset($settings['https'])) {
            $this->https($settings['https']);
        }
        if (isset($settings['size'])) {
            $this->size($settings['size']);
        }
        if (isset($settings['hd'])) {
            $this->hd($settings['hd']);
        }
        if (isset($settings['autoplay'])) {
            $this->autoplay($settings['autoplay']);
        }
        if (isset($settings['jsapi'])) {
            $this->jsAPI($settings['jsapi']);
        }
        if (isset($settings['progressbar'])) {
            $this->progressBar($settings['progressbar']);
        }
        if (isset($settings['timecode'])) {
            $this->timeCode($settings['timecode']);
        }
        if (isset($settings['cookies'])) {
            $this->cookies($settings['cookies']);
        }
        if (isset($settings['theme'])) {
            $this->theme($settings['theme']);
        }
        if (isset($settings['loop'])) {
            $this->loop($settings['loop']);
        }
    }

    /**
     * Factory
     * Allows easy creation and daisy-chaining of a YTAudio object.
     *
     * @see __construct
     *
     * @param string $source
     * @param array  $settings
     *
     * @throws AudioPlayerException
     *
     * @return static
     */
    public static function create($source, $settings = null)
    {
        return new static($source, $settings);
    }

    /**
     * Set Player Video/Playlist
     * Does not validate whether or not YouTube video/playlist exists.
     *
     *
     * @param string $source Can be a URL or just the ID
     *
     * @throws AudioPlayerException
     *
     * @return $this
     */
    public function source($source)
    {
        $oldSource = $this->source;

        $this->source = $source;
        $parsedURL = parse_url($source);

        // 1 thing, auto-detect based on ID
        if (count($parsedURL) === 1) {
            if (substr(strtoupper($parsedURL['path']), 0, 2) == 'PL') {
                return $this->playlist($source);
            } else {
                return $this->video($source);
            }
        }

        // Else, try to detect in turn.
        try {
            return $this->video($source);
        } catch (AudioPlayerException $e) {
        } // do nothing.  Might be playlist.

        try {
            return $this->playlist($source);
        } catch (AudioPlayerException $e) {
            $this->source = $oldSource;
            throw new AudioPlayerException('Could not detect source');
        }
    }

    /**
     * Set Player Video
     * Does not validate whether or not YouTube video exists.
     *
     *
     * @param string $video Can be a URL or just the ID
     *
     * @throws AudioPlayerException
     *
     * @return $this
     */
    public function video($video)
    {
        $oldSource = $this->source;
        $this->source = $video;

        $parsedURL = parse_url($video);

        // 1 thing, assume video.
        if (count($parsedURL) === 1) {
            $this->type = static::TYPE_VIDEO;
            $this->id = $parsedURL['path'];

            return $this;
        }

        // Youtu.be - Video
        if (strtolower($parsedURL['host']) == 'youtu.be') {
            $this->type = static::TYPE_VIDEO;
            $this->id = substr($parsedURL['path'], 1);

            return $this;
        }

        // Assume its a YouTube URL
        // check for /watch
        if (strtolower($parsedURL['path']) == '/watch') {
            $parsedQuery = explode('&', $parsedURL['query']);
            // Find v=
            foreach ($parsedQuery as $v) {
                if (substr(strtolower($v), 0, 2) == 'v=') {
                    $this->type = static::TYPE_VIDEO;
                    $this->id = substr($v, 2);

                    return $this;
                }
            }
        }

        // check for /v/
        if (substr(strtolower($parsedURL['path']), 0, 3) == '/v/') {
            $this->type = static::TYPE_VIDEO;
            $this->id = substr($parsedURL['path'], 0, 3);

            return $this;
        }

        $this->source = $oldSource;
        throw new AudioPlayerException('Could not identify video');
    }

    /**
     * Set Player Playlist
     * Does not validate whether or not YouTube playlist exists.
     *
     *
     * @param string $playlist Can be a URL or just the ID
     *
     * @throws AudioPlayerException
     *
     * @return $this
     */
    public function playlist($playlist)
    {
        $oldSource = $this->source;
        $this->source = $playlist;

        $parsedURL = parse_url($playlist);

        // 1 thing, assume playlist.
        if (count($parsedURL) === 1) {
            $this->type = static::TYPE_PLAYLIST;
            $this->id = $parsedURL['path'];

            return $this->cookies()->theme(static::THEME_LIGHT);
        }

        // both playlist types use list=

        $parsedQuery = explode('&', $parsedURL['query']);
        // Find list=
        foreach ($parsedQuery as $v) {
            if (substr(strtolower($v), 0, 5) == 'list=') {
                $this->type = static::TYPE_PLAYLIST;
                $this->id = substr($v, 5);

                return $this->cookies()->theme(static::THEME_LIGHT);
            }
        }

        // If we don't find list=, then its not a playlist.
        $this->source = $oldSource;
        throw new AudioPlayerException('Could not identify playlist');
    }

    /**
     * Get Source
     * Returns the source exactly as you fed it to us.
     * The source is only changed if setting it was successful.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get Player Type.
     *
     * @return int/bool
     */
    public function getType()
    {
        return $this->type;
    }

    public function isVideo()
    {
        return $this->getType() == static::TYPE_VIDEO;
    }

    public function isPlaylist()
    {
        return $this->getType() == static::TYPE_PLAYLIST;
    }

    /**
     * Get Player ID.
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Set Player Size.
     *
     *
     * @param int $size [SIZE_INVISIBLE | SIZE_TINY | SIZE_SMALL | SIZE_MEDIUM | SIZE_LARGE]
     *
     * @throws AudioPlayerException
     *
     * @return $this
     */
    public function size($size)
    {
        if (!$this->isValidSize($size)) {
            throw new AudioPlayerException('Invalid Size');
        }
        $this->size = $size;

        // Progress Bar & Time Code can not be used with Tiny/Invisible
        // Any other size (Small, Medium, Large) MUST have a Progress Bar
        if ($size == static::SIZE_TINY || $size == static::SIZE_INVISIBLE) {
            $this->progressBar(false)->timeCode(false);
        } else {
            $this->progressBar();
        }

        return $this;
    }

    protected function isValidSize($size)
    {
        return in_array(
            $size,
            [static::SIZE_INVISIBLE, static::SIZE_TINY, static::SIZE_SMALL, static::SIZE_MEDIUM, static::SIZE_LARGE]
        );
    }

    /**
     * Get Player Size.
     *
     * @return int/bool
     */
    public function getSize()
    {
        return $this->size;
    }

    public function isTiny()
    {
        return $this->getSize() == static::SIZE_TINY;
    }

    public function isSmall()
    {
        return $this->getSize() == static::SIZE_SMALL;
    }

    public function isMedium()
    {
        return $this->getSize() == static::SIZE_MEDIUM;
    }

    public function isLarge()
    {
        return $this->getSize() == static::SIZE_LARGE;
    }

    /**
     * Set Player Invisible
     * Convenience function, since Invisibility is a SIZE.
     *
     * @return $this
     */
    public function invisible()
    {
        $this->size(static::SIZE_INVISIBLE);
    }

    /**
     * Get Player Invisibility Setting.
     *
     * @return bool
     */
    public function getInvisible()
    {
        return $this->getSize() == static::SIZE_INVISIBLE;
    }

    public function isInvisible()
    {
        return $this->getInvisible();
    }

    /**
     * Set Player Theme.
     *
     *
     * @param int $theme [THEME_LIGHT | THEME_DARK]
     *
     * @throws AudioPlayerException
     *
     * @return $this
     */
    public function theme($theme)
    {
        if ($this->isPlaylist() && $theme == static::THEME_DARK) {
            throw new AudioPlayerException('Playlists can not use the Dark Theme.  YouTube limitation.');
        }
        if ($theme != static::THEME_LIGHT && $theme != static::THEME_DARK) {
            throw new AudioPlayerException('Invalid Theme');
        }
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get Player Theme.
     *
     * @return bool
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set HD
     * Choose whether or not to force the player into HD.
     *
     * @param bool $useHD
     *
     * @return $this
     */
    public function hd($useHD = true)
    {
        if ($useHD) {
            $this->hd = true;
        } else {
            $this->hd = false;
        }

        return $this;
    }

    /**
     * Get HD Setting.
     *
     * @return bool
     */
    public function getHD()
    {
        return $this->hd;
    }

    public function isHD()
    {
        return $this->getHD();
    }

    /**
     * Set Autoplay
     * Choose whether or not to automatically play the video when it loads
     * Please don't use this.  You'll make me sad.
     *
     * @param bool $autoplay
     *
     * @return $this
     */
    public function autoplay($autoplay = true)
    {
        if ($autoplay) {
            $this->autoplay = true;
        } else {
            $this->autoplay = false;
        }

        return $this;
    }

    /**
     * Get Autoplay Setting.
     *
     * @return bool
     */
    public function getAutoplay()
    {
        return $this->autoplay;
    }

    public function willAutoplay()
    {
        return $this->getAutoplay();
    }

    /**
     * Set JSApi
     * Choose whether or not to allow access via the YouTube JavaScript API.
     *
     * @param bool $useJSAPI
     *
     * @return $this
     */
    public function jsAPI($useJSAPI = true)
    {
        if ($useJSAPI) {
            $this->jsapi = true;
        } else {
            $this->jsapi = false;
        }

        return $this;
    }

    /**
     * Get JavaScript API Setting.
     *
     * @return bool
     */
    public function getJSAPI()
    {
        return $this->jsapi;
    }

    public function canUseJSAPI()
    {
        return $this->getJSAPI();
    }

    public function canUseJavaScriptAPI()
    {
        return $this->getJSAPI();
    }

    /**
     * Set Loop
     * Choose whether or not to loop once the video/playlist is over.
     *
     * @param bool $loop
     *
     * @return $this
     */
    public function loop($loop = true)
    {
        if ($loop) {
            $this->loop = true;
        } else {
            $this->loop = false;
        }

        return $this;
    }

    /**
     * Get Loop Setting.
     *
     * @return bool
     */
    public function getLoop()
    {
        return $this->loop;
    }

    public function willLoop()
    {
        return $this->getLoop();
    }

    /**
     * Set Progress Bar
     * Choose whether or not to display the progress bar.
     *
     * @param bool $useProgressBar
     *
     * @return $this
     */
    public function progressBar($useProgressBar = true)
    {
        if ($useProgressBar) {
            $this->progressbar = true;
        } else {
            $this->progressbar = false;
        }

        // If they set this true after saying they want tiny, they actually want small.
        if ($useProgressBar && $this->getSize() == static::SIZE_TINY) {
            $this->size(static::SIZE_SMALL);
        }
        // If they set this true after saying they want invisible, they're being silly and I refuse to handle it.

        return $this;
    }

    /**
     * Get Progress Bar Setting.
     *
     * @return bool
     */
    public function getProgressBar()
    {
        return $this->progressbar;
    }

    public function hasProgressBar()
    {
        return $this->getProgressBar();
    }

    /**
     * Set Time Code
     * Choose whether or not to display the time code.  Requires Progress Bar.
     *
     * @param bool $useTimeCode
     *
     * @return $this
     */
    public function timeCode($useTimeCode = true)
    {
        if ($useTimeCode) {
            // Requires Progress Bar.  Sorry.
            $this->progressBar();
            $this->timecode = true;
        } else {
            $this->timecode = false;
        }

        // If they set this true after saying they want tiny, they actually want small.
        if ($this->progressbar && $this->getSize() == static::SIZE_TINY) {
            $this->size(static::SIZE_SMALL);
        }
        // If they set this true after saying they want invisible, they're being silly and I refuse to handle it.

        return $this;
    }

    /**
     * Get Time Code Setting.
     *
     * @return bool
     */
    public function getTimeCode()
    {
        return $this->timecode;
    }

    public function hasTimeCode()
    {
        return $this->getTimeCode();
    }

    /**
     * Set Cookies
     * Choose whether or not to allow YouTube to collect cookies.
     *
     * @param bool $useCookies
     *
     * @throws AudioPlayerException
     *
     * @return $this
     */
    public function cookies($useCookies = true)
    {
        if (!$useCookies && $this->getType() == static::TYPE_PLAYLIST) {
            throw new AudioPlayerException('Can not disable cookies with playlists.  YouTube limitation.');
        }

        if ($useCookies) {
            $this->cookies = true;
        } else {
            $this->cookies = false;
        }

        return $this;
    }

    /**
     * Get Cookie Setting.
     *
     * @return bool
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    public function willUseCookies()
    {
        return $this->getCookies();
    }

    /**
     * Set HTTPS
     * Choose whether to use HTTPs or HTTP.
     *
     * @param bool $useHTTPS
     *
     * @return $this
     */
    public function https($useHTTPS = true)
    {
        if ($useHTTPS) {
            $this->https = true;
        } else {
            $this->https = false;
        }

        return $this;
    }

    /**
     * Get HTTPS Setting.
     *
     * @return bool
     */
    public function getHTTPS()
    {
        return $this->https;
    }

    public function isHTTPS()
    {
        return $this->https;
    }

    public function isHTTP()
    {
        return !$this->https;
    }

    /**
     * Get Height (px).
     *
     * @return int
     */
    public function getHeight()
    {
        if ($this->getSize() == static::SIZE_INVISIBLE) {
            return 1;
        }

        return 25;
    }

    /**
     * Get Width (px).
     *
     * @return int
     */
    public function getWidth()
    {
        if ($this->getSize() == static::SIZE_INVISIBLE) {
            return 1;
        }
        if ($this->getSize() == static::SIZE_TINY) {
            return 30;
        }

        $modifier = 0;
        if ($this->getTimeCode()) {
            $modifier = 75;
        }

        if ($this->getSize() == static::SIZE_SMALL) {
            return 150 + $modifier;
        }
        if ($this->getSize() == static::SIZE_MEDIUM) {
            return 187 + $modifier;
        }
        // static::SIZE_LARGE
        return 224 + $modifier;
    }

    /**
     * Get Embed URL.
     *
     * @param bool $encode
     *
     * @return string
     */
    public function getEmbedURL($encode = true)
    {
        $url = '';

        // PROTOCOL
        if ($this->getHTTPS()) {
            $url .= 'https://';
        } else {
            $url .= 'http://';
        }

        // DOMAIN
        if ($this->getCookies()) {
            $url .= 'www.youtube.com';
        } else {
            $url .= 'www.youtube-nocookie.com';
        }

        // PATH
        if ($this->isVideo()) {
            $url .= '/v/';
        } else {
            $url .= '/p/';
        }

        // ID
        if ($this->isVideo()) {
            $url .= $this->getID();
        } else {
            $url .= substr($this->getID(), 2);
        } // Playlists start with PL but YouTube doesn't want that

        // Build Query String
        $query = [];
        $query['version'] = 2;
        if ($this->getAutoplay()) {
            $query['autoplay'] = 1;
        }
        if ($this->getLoop()) {
            $query['loop'] = 1;
        }
        if ($this->getJSAPI()) {
            $query['enablejsapi'] = 1;
        }
        if ($this->isHD()) {
            $query['hd'] = 1;
        }
        $query['theme'] = $this->getTheme();

        $seperator = $encode ? '&amp;' : '&';
        $url .= '?'.http_build_query($query, $seperator);

        return $url;
    }

    /**
     * Render valid XHTML.
     *
     * @param bool $return Return the HTML instead of echoing it.
     *
     * @return string|bool
     */
    public function render($return = false)
    {
        // Build the string
        $html = '<object type="application/x-shockwave-flash"';
        $html .= ' width="'.$this->getWidth().'"';
        $html .= ' height="'.$this->getHeight().'"';
        $html .= ' data="'.$this->getEmbedURL().'"';
        if ($this->isInvisible()) {
            $html .= ' style="visibility:hidden;display:inline;"';
        }
        $html .= '>';
        $html .= '<param name="movie" value="'.$this->getEmbedURL().'" />';
        $html .= '<param name="wmode" value="transparent" />';
        $html .= '</object>';

        if ($return) {
            return $html;
        }
        echo $html;

        return true;
    }
}
