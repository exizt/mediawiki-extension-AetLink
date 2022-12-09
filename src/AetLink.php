<?php
/**
 * AetLink
 *
 * @link https://github.com/exizt/mw-ext-AetLink
 * @author exizt
 * @license GPL-2.0-or-later
 */
class AetLink {
	# 설정값을 갖게 되는 멤버 변수
	private static $config = null;

	/**
	 * 'LinkerMakeExternalLink' 훅
	 * 
	 * '외부 링크'의 HTML을 처리하는 파서. 
	 * 'false'를 반환해야 변경된 html이 적용된다고 함.
	 * 
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LinkerMakeExternalLink
	 */
	public static function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs, $linktype ) { 
		# 설정 로드
		$config = self::getConfiguration();

		# 외부 링크를 해제하는 옵션 사용시
		if($config['disable_external_link']){
			global $wgServer;
			# 문자열 앞부분을 확인해서, 서버 설정과 동일한 경우. (즉, 외부 연결이 아닌 경우)
			# 링크를 해제하지 않도록 함.
			# strpos를 사용했지만, php 8 이후에는 str_starts_with(haystack, needle) 함수가 있다.
			if(strpos($url, $wgServer) === 0){
			# if(substr_compare($url, $wgServer, 0, strlen($wgServer)) === 0){
				return;
			}
			# 링크를 해제하고 문자열로 치환함.
			# $url = '';
			# $attribs['target'] = '';
			$link = "<span data-origin-href='{$url}'>{$text}</span>";

			# false로 반환해야 변경이 적용된다고 함.
			return false;
		}
		return;
	}

	/**
	 * 'HtmlPageLinkRendererEnd' 훅
	 * 
	 * 문서 페이지에서 링크가 있을 때, 렌더링을 처리하는 파서.
	 * 링크의 갯수만큼 시행된다.
	 * 
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/HtmlPageLinkRendererEnd
	 */
	public static function onHtmlPageLinkRendererEnd( $linkRenderer, $target, $isKnown, &$text, &$attribs, &$ret ){
		# self::debugLog('::onHtmlPageLinkRendererEnd');
		
		self::addInterwikiLinkTarget($attribs);
		
		return true;
	}

	/**
	 * 인터위키 링크의 타겟을 변경하는 메소드.
	 */
	private static function addInterwikiLinkTarget( &$attribs ){
		# class 속성이 있는지 확인. 이게 없으면 뭔가 비교하기가 어려우므로...
		if ( array_key_exists( 'class', $attribs ) ) {
			$class = $attribs['class'];
		} else {
			return;
		}

		# interwiki는 'extiw' 클래스를 갖고 있으므로, 이것이 없는 경우는 처리하지 않음.
		if ( strpos( $class, 'extiw' ) === false ) {
			return;
		}

		self::debugLog('::addInterwikiLinkTarget');

		# 설정 로드
		$config = self::getConfiguration();
		if ($config['interwiki_link_target'] && strlen($config['interwiki_link_target']) > 2 ){
			# 인터위키의 target="_blank"로 변경
			# 인터위키의 링크 타겟을 지정할 수 있게 함.
			$attribs['target'] = $config['interwiki_link_target'];
			$attribs['rel'] = 'noreferrer noopener';
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

		# self::debugLog($config);
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
