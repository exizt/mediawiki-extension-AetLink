<?php
/**
 * AetLink
 *
 * @link https://github.com/exizt/mw-ext-AetLink
 * @author exizt
 * @license GPL-2.0-or-later
 */

# namespace MediaWiki\Extension\AetLink;

class AetLink {
	# 설정값을 갖게 되는 멤버 변수
	private static $config = null;

	/**
	 * 'BeforePageDisplay' 훅.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		# global $wgExtEzxCustomDisableLinks;
		# $conf = array();
		# $conf['disableLink'] = isset($wgExtEzxCustomDisableLinks) ? $wgExtEzxCustomDisableLinks: true;
		self::debugLog('::onBeforePageDisplay');

		# 설정 로드
		$conf = self::getConfiguration();
		# self::debugLog($conf);

		# 링크 해제하는 스크립트 사용
		if($conf['disable_external_link']){
			///$modules[] = 'ext.AetCustom.disablelinks';
		}

		# 리소스모듈 추가
		//$out->addModules( $modules );
	}

	/**
	 * 
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LinkerMakeExternalLink
	 */
	public static function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs, $linktype ) { 
		# 설정 로드
		$conf = self::getConfiguration();

		# 링크 해제하는 스크립트 사용
		if($conf['disable_external_link']){
			# $url = '';
			# $attribs['target'] = '';
			$link = "{$text}";
		}
		return false;
	}

	/**
	 * 
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/HtmlPageLinkRendererEnd
	 */
	public static function onHtmlPageLinkRendererEnd( $linkRenderer, $target, $isKnown, &$text, &$attribs, &$ret ){
		# self::debugLog('::onHtmlPageLinkRendererEnd');
		
		self::addInterwikiLinkTarget($attribs);
		
		return true;
	}

	private static function addInterwikiLinkTarget( &$attribs ){
		# class 속성이 있는지 확인. 이게 없으면 뭔가 비교하기가 어려우므로...
		if ( array_key_exists( 'class', $attribs ) ) {
			$class = $attribs['class'];
		} else {
			return;
		}
		self::debugLog('::addInterwikiLinkTarget');

		# 설정 로드
		$config = self::getConfiguration();
		self::debugLog($config);
	
		# 인터위키의 target="_blank"로 변경
		# 인터위키의 링크 타겟을 지정할 수 있게 함.
		if ($config['interwiki_link_target'] && strlen($config['interwiki_link_target']) > 2 ){

			# 인터위키의 target="_blank"로 변경
			# 인터위키의 링크 타겟을 지정할 수 있게 함.
			if ( strpos( $class, 'extiw' ) > -1 ) {
				$attribs['target'] = $config['interwiki_link_target'];
				$attribs['rel'] = 'noreferrer noopener';
			}
		}

	}

	/**
	 * 설정을 로드함.
	 */
	private static function getConfiguration(){
		# 한 번 로드했다면, 그 후에는 로드하지 않도록 처리.
		if( ! is_null(self::$config) ){
			return self::$config;
		}
		self::debugLog('::getConfiguration');

		# 설정 기본값
		$config = [
			'debug' => false,
			'disable_external_link' => false,
			'interwiki_link_target' => ''
		];

		# 설정값 병합
		$userSettings = self::getUserLocalSettings();
		if (isset($userSettings)){
			# 만약을 위한 설정값 타입 체크.
			foreach ($userSettings as $key => $value) {
				if( array_key_exists($key, $config) ) {
					if( gettype($config[$key]) == gettype($value) ){
						$config[$key] = $value;
					} else {
						self::debugLog($key.'옵션값이 잘못되었습니다.');
					}
				}
			}
		}

		self::$config = $config;
		return $config;
	}

	/**
	 * 설정값 조회
	 */
	private static function getUserLocalSettings(){
		global $wgAetLink;
		return $wgAetLink;
	}

	/**
	 * 디버그 로깅 관련
	 */
	private static function debugLog($msg){
		global $wgDebugToolbar;

		# 디버그툴바 사용중일 때만 허용.
		$useDebugToolbar = $wgDebugToolbar ?? false;
		if( !$useDebugToolbar ){
			return false;
		}
		
		# 로깅
		$userSettings = self::getUserLocalSettings();
		$isDebug = $userSettings['debug'] ?? false;
		if($isDebug){
			if(is_string($msg)){
				wfDebugLog(static::class, $msg);
			} else {
				wfDebugLog(static::class, json_encode($msg));
			}
		} else {
			return false;
		}
	}
}
