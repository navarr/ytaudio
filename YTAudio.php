<?php

/**
 * @author Navarr Barnier <me@navarr.me>
 * @license MIT
 */
class YTAudio
{
	const SIZE_INVISIBLE = 0;
	const SIZE_TINY = 1;
	const SIZE_SMALL = 2;
	const SIZE_MEDIUM = 3;
	const SIZE_LARGE = 4;
	
	const THEME_LIGHT = "light";
	const THEME_DARK = "dark";
	
	const TYPE_VIDEO = 1;
	const TYPE_PLAYLIST = 2;
	
	protected $_id = null;
	protected $_https = true;
	protected $_type = self::TYPE_VIDEO;
	protected $_size = self::SIZE_SMALL;
	protected $_source = null;
	protected $_hd = false;
	protected $_autoplay = false;
	protected $_jsapi = false;
	protected $_progressbar = false;
	protected $_timecode = false;
	protected $_cookies = false;
	protected $_theme = self::THEME_DARK;
	protected $_loop = false;
	
	/** 
	 * Constructor
	 *
	 * @see size
	 * @see theme
	 *
	 * @throws YTAudioException
	 * @param string $source
	 * @param enum $size
	 * @param enum $theme
	 */
	public function __construct($source, $size = self::SIZE_SMALL,$theme = self::THEME_LIGHT)
	{
		$this->source($source)->size($size)->theme($theme);
	}
	
	/**
	 * Factory
	 * Allows easy creation and daisy-chaining of a YTAudio object.
	 *
	 * @see size
	 * @see theme
	 * 
	 * @throws YTAudioException
	 * @param string $source
	 * @param enum $size
	 * @param enum $theme
	 * @return YTAudio
	 */
	public static function create($source, $size = self::SIZE_SMALL,$theme = self::THEME_LIGHT)
	{
		$self = new self($source,$size,$theme);
		return $self;
	}
	
	/**
	 * Set Player Video/Playlist
	 * Does not validate whether or not YouTube video/playlist exists.
	 *
	 * @throws YTAudioException
	 * @param string $source Can be a URL or just the ID
	 * @return YTAudio
	 */
	public function source($source)
	{
		$oldSource = $this->_source;
		
		$this->_source = $source;
		$parsedURL = parse_url($source);
		
		// 1 thing, auto-detect based on ID
		if(count($parsedURL) === 1)
		{
			if(substr(strtoupper($parsedURL['path']),0,2) == "PL") return $this->playlist($source);
			else return $this->video($source);
		}
		
		// Else, try to detect in turn.
		try
		{
			return $this->video($source);
		}
		catch(YTAudioException $e) { } // do nothing.  Might be playlist.
		
		try
		{
			return $this->playlist($source);
		}
		catch(YTAudioException $e)
		{
			$this->_source = $oldSource;
			throw new YTAudioException("Could not detect source");
		}
	}
	
	/**
	 * Set Player Video
	 * Does not validate whether or not YouTube video exists.
	 *
	 * @throws YTAudioException
	 * @param string $video Can be a URL or just the ID
	 * @return YTAudio
	 */
	public function video($video)
	{
		$oldSource = $this->_source;
		$this->_source = $video;
		
		$parsedURL = parse_url($video);
		
		// 1 thing, assume video.
		if(count($parsedURL) === 1)
		{
			$this->_type = self::TYPE_VIDEO;
			$this->_id = $parsedURL['path'];
			return $this;
		}
		
		// Youtu.be - Video
		if(strtolower($parsedURL['host']) == "youtu.be")
		{
			$this->_type = self::TYPE_VIDEO;
			$this->_id = substr($parsedURL['path'],1);
			return $this;
		}
		
		// Assume its a YouTube URL
		// check for /watch
		if(strtolower($parsedURL['path']) == "/watch")
		{
			$parsedQuery = explode("&",$parsedURL['query']);
			// Find v=
			foreach($parsedQuery as $v)
			{
				if(substr(strtolower($v),0,2) == "v=")
				{
					$this->_type = self::TYPE_VIDEO;
					$this->_id = substr($v,2);
					return $this;
				}
			}
		}
		
		// check for /v/
		if(substr(strtolower($parsedURL['path']),0,3) == "/v/")
		{
			$this->_type = self::TYPE_VIDEO;
			$this->_id = substr($parsedURL['path'],0,3);
			return $this;
		}
		
		$this->_source = $oldSource;
		throw new YTAudioException("Could not identify video");
	}

	/**
	 * Set Player Playlist
	 * Does not validate whether or not YouTube playlist exists.
	 *
	 * @throws YTAudioException
	 * @param string $playlist Can be a URL or just the ID
	 * @return YTAudio
	 */
	public function playlist($playlist)
	{
		$oldSource = $this->_source;
		$this->_source = $playlist;
		
		$parsedURL = parse_url($playlist);
		
		// 1 thing, assume playlist.
		if(count($parsedURL) === 1)
		{
			$this->_type = self::TYPE_PLAYLIST;
			$this->_id = $parsedURL['path'];
			return $this->cookies()->theme(self::THEME_LIGHT);
		}
		
		// both playlist types use list=
		
		$parsedQuery = explode("&",$parsedURL['query']);
		// Find list=
		foreach($parsedQuery as $v)
		{
			if(substr(strtolower($v),0,5) == "list=")
			{
				$this->_type = self::TYPE_PLAYLIST;
				$this->_id = substr($v,5);
				return $this->cookies()->theme(self::THEME_LIGHT);
			}
		}

		// If we don't find list=, then its not a playlist.
		$this->_source = $oldSource;
		throw new YTAudioException("Could not identify playlist");
	}
	
	/**
	 * Get Source
	 * Returns the source exactly as you fed it to us.
	 * The source is only changed if setting it was successful.
	 *
	 * @return string
	 */
	public function getSource() { return $this->_source; }
	
	/**
	 * Get Player Type
	 *
	 * @return enum/bool
	 */
	public function getType() { return $this->_type; }
	public function isVideo() { return ($this->getType() == self::TYPE_VIDEO); }
	public function isPlaylist() { return ($this->getType() == self::TYPE_PLAYLIST); }
	
	/**
	 * Get Player ID
	 *
	 * @return string
	 */
	public function getID() { return $this->_id; }
	
	/**
	 * Set Player Size
	 * 
	 * @throws YTAudioException
	 * @param enum $size [SIZE_INVISIBLE | SIZE_TINY | SIZE_SMALL | SIZE_MEDIUM | SIZE_LARGE]
	 * @return YTAudio
	 */
	public function size($size)
	{
		if($size != self::SIZE_INVISIBLE && $size != self::SIZE_TINY && $size != self::SIZE_SMALL && $size != self::SIZE_MEDIUM && $size != self::SIZE_LARGE) throw new YTAudioException("Invalid Size");
		$this->_size = $size;
		
		// Progress Bar & Time Code can not be used with Tiny/Invisible
		// Any other size (Small, Medium, Large) MUST have a Progress Bar
		if($size == self::SIZE_TINY || $size == self::SIZE_INVISIBLE) $this->progressBar(false)->timeCode(false);
		else $this->progressBar();
		
		return $this;
	}
	
	/**
	 * Get Player Size
	 *
	 * @return enum/bool
	 */
	public function getSize() { return $this->_size; }
	public function isTiny() { return ($this->getSize() == self::SIZE_TINY); }
	public function isSmall() { return ($this->getSize() == self::SIZE_SMALL); }
	public function isMedium() { return ($this->getSize() == self::SIZE_MEDIUM); }
	public function isLarge() { return ($this->getSize() == self::SIZE_LARGE); }
	
	/**
	 * Set Player Invisible
	 * Convenience function, since Invisibility is a SIZE
	 *
	 * @return YTAudio
	 */
	public function invisible() { $this->size(self::SIZE_INVISIBLE); }
	
	/**
	 * Get Player Invisibility Setting
	 *
	 * @return bool
	 */
	public function getInvisible() { return ($this->getSize() == self::SIZE_INVISIBLE); }
	public function isInvisible() { return $this->getInvisible(); }
	
	/**
	 * Set Player Theme
	 *
	 * @throws YTAudioException
	 * @param enum $theme [THEME_LIGHT | THEME_DARK]
	 * @return YTAudio
	 */
	public function theme($theme)
	{
		if($this->isPlaylist() && $theme == self::THEME_DARK) throw new YTAudioException("Playlists can not use the Dark Theme.  YouTube limitation.");
		if($theme != self::THEME_LIGHT && $theme != self::THEME_DARK) throw new YTAudioException("Invalid Theme");
		$this->_theme = $theme;
		return $this;
	}
	
	/**
	 * Get Player Theme
	 *
	 * @return bool
	 */
	public function getTheme() { return $this->_theme; }
	
	/**
	 * Set HD
	 * Choose whether or not to force the player into HD
	 *
	 * @param bool $useHD
	 * @return YTAudio
	 */
	public function hd($useHD = true)
	{
		if($useHD) $this->_hd = true;
		else $this->_hd = false;
		return $this;
	}
	
	/**
	 * Get HD Setting
	 *
	 * @return bool
	 */
	public function getHD() { return $this->_hd; }
	public function isHD() { return $this->getHD(); }
	
	/**
	 * Set Autoplay
	 * Choose whether or not to automatically play the video when it loads
	 * Please don't use this.  You'll make me sad.
	 *
	 * @param bool $autoplay
	 * @return YTAudio
	 */
	public function autoplay($autoplay = true)
	{
		if($autoplay) $this->_autoplay = true;
		else $this->_autoplay = false;
		return $this;
	}
	
	/**
	 * Get Autoplay Setting
	 *
	 * @return bool
	 */
	public function getAutoplay() { return $this->_autoplay; }
	public function willAutoplay() { return $this->getAutoplay(); }
	
	/**
	 * Set JSApi
	 * Choose whether or not to allow access via the YouTube JavaScript API
	 *
	 * @param bool $useJSAPI
	 * @return YTAudio
	 */
	public function jsAPI($useJSAPI = true)
	{
		if($useJSAPI) $this->_jsapi = true;
		else $this->_jsapi = false;
		return $this;
	}
	
	/**
	 * Get JavaScript API Setting
	 *
	 * @return bool
	 */
	public function getJSAPI() { return $this->_jsapi; }
	public function canUseJSAPI() { return $this->getJSAPI(); }
	public function canUseJavaScriptAPI() { return $this->getJSAPI(); }
	
	/**
	 * Set Loop
	 * Choose whether or not to loop once the video/playlist is over
	 *
	 * @param bool $loop
	 * @return YTAudio
	 */
	public function loop($loop = true)
	{
		if($loop) $this->_loop = true;
		else $this->loop = false;
		return $this;
	}
	
	/**
	 * Get Loop Setting
	 *
	 * @return bool
	 */
	public function getLoop() { return $this->_loop; }
	public function willLoop() { return $this->getLoop(); }
	
	/**
	 * Set Progress Bar
	 * Choose whether or not to display the progress bar
	 *
	 * @param bool $useProgressBar
	 * @return YTAudio
	 */
	public function progressBar($useProgressBar = true)
	{
		if($useProgressBar) $this->_progressbar = true;
		else $this->_progressbar = false;
		
		// If they set this true after saying they want tiny, they actually want small.
		if($useProgressBar && $this->getSize() == self::SIZE_TINY) $this->size(self::SIZE_SMALL);
		// If they set this true after saying they want invisible, they're being silly and I refuse to handle it.
		
		return $this;
	}
	
	/**
	 * Get Progress Bar Setting
	 *
	 * @return bool
	 */
	public function getProgressBar() { return $this->_progressbar; }
	public function hasProgressBar() { return $this->getProgressBar(); }
	
	/**
	 * Set Time Code
	 * Choose whether or not to display the time code.  Requires Progress Bar
	 *
	 * @param bool $useTimeCode
	 * @return YTAudio
	 */
	public function timeCode($useTimeCode = true)
	{
		if($useTimeCode)
		{
			// Requires Progress Bar.  Sorry.
			$this->progressBar();
			$this->_timecode = true;
		}
		else $this->_timecode = false;
		
		// If they set this true after saying they want tiny, they actually want small.
		if($useProgressBar && $this->getSize() == self::SIZE_TINY) $this->size(self::SIZE_SMALL);
		// If they set this true after saying they want invisible, they're being silly and I refuse to handle it.
		
		return $this;
	}
	
	/**
	 * Get Time Code Setting
	 *
	 * @return bool
	 */
	public function getTimeCode() { return $this->_timecode; }
	public function hasTimeCode() { return $this->getTimeCode(); }
	
	/**
	 * Set Cookies
	 * Choose whether or not to allow YouTube to collect cookies.
	 *
	 * @param bool $useCookies
	 * @return YTAudio
	 */
	public function cookies($useCookies = true)
	{
		if(!$useCookies && $this->getType() == self::TYPE_PLAYLIST) throw new YTAudioException("Can not disable cookies with playlists.  YouTube limitation.");

		if($useCookies) $this->_cookies = true;
		else $this->_cookies = false;
		return $this;
	}
	
	/**
	 * Get Cookie Setting
	 *
	 * @return bool
	 */
	public function getCookies() { return $this->_cookies; }
	public function willUseCookies() { return $this->getCookies(); }
	
	/**
	 * Get HTTPS Setting
	 *
	 * @return bool
	 */
	public function getHTTPS() { return $this->_https; }
	public function isHTTPS() { return $this->_https; }
	
	/**
	 * Get Height (px)
	 *
	 * @return int
	 */
	public function _getHeight()
	{ 
		if($this->getSize() == self::SIZE_INVISIBLE) return 1;
		return 25;
	}
	
	/**
	 * Get Width (px)
	 *
	 * @return int
	 */
	public function _getWidth()
	{
		if($this->getSize() == self::SIZE_INVISIBLE) return 1;
		if($this->getSize() == self::SIZE_TINY) return 30;
		
		$modifier = 0;
		if($this->getTimeCode()) $modifier = 75;
		
		if($this->getSize() == self::SIZE_SMALL)  return 150 + $modifier;
		if($this->getSize() == self::SIZE_MEDIUM) return 187 + $modifier;
		if($this->getSize() == self::SIZE_LARGE)  return 224 + $modifier;
	}
	
	/**
	 * Get Embed URL
	 *
	 * @return string
	 */
	public function _getEmbedURL($encode = TRUE)
	{
		$url = "";
		
		// PROTOCOL
		if($this->getHTTPS()) $url .= "https://";
		else $url .= "http://";
		
		// DOMAIN
		if($this->getCookies()) $url .= "www.youtube.com";
		else $url .= "www.youtube-nocookie.com";
		
		// PATH
		if($this->isVideo()) $url .= "/v/";
		else $url .= "/p/";
		
		// ID
		if($this->isVideo()) $url .= $this->getID();
		else $url .= substr($this->getID(),2); // Playlists start with PL but YouTube doesn't want that
		
		// Build Query String
		$query = array();
		$query['version'] = 2;
		if($this->getAutoplay()) $query['autoplay'] = 1;
		if($this->getLoop()) $query['loop'] = 1;
		if($this->getJSAPI()) $query['enablejsapi'] = 1;
		if($this->isHD()) $query['hd'] = 1;
		$query['theme'] = $this->getTheme();
		
		$seperator = $encode ? '&amp;' : '&';
		$url .= '?' . http_build_query($query,$seperator);
		
		return $url;
	}
	
	/**
	 * Render valid XHTML
	 *
	 * @param bool $return Return the HTML instead of echoing it.
	 * @return string
	 */
	public function render($return = false)
	{
		// Build the string
		$html  = '<object type="application/x-shockwave-flash"';
		$html .= ' width="' . $this->_getWidth() . '"';
		$html .= ' height="' . $this->_getHeight() . '"';
		$html .= ' data="' . $this->_getEmbedURL() . '"';
		if($this->isInvisible()) $html .= ' style="visibility:hidden;display:inline;"';
		$html .= '>';
		$html .= 	'<param name="movie" value="' . $this->_getEmbedURL() . '" />';
		$html .=	'<param name="wmode" value="transparent" />';
		$html .= '</object>';
		
		if($return) return $html;
		echo $html;
	}
}
class YTAudioException extends Exception
{
	public function __construct($message,$code = 0)
	{
		parent::__construct($message,$code);
	}
}