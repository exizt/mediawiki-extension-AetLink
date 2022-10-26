ready(function(){
	disableExternalLinks();

	/**
	* [문서보기 화면] 에서, 외부 링크 해제.
	*/
	function disableExternalLinks()
	{
		//var externalLinks = document.querySelectorAll('#content a.external');
		var externalLinks = document.querySelectorAll('#mw-content-text .mw-parser-output a.external');
		if(externalLinks !== null){
			externalLinks.forEach(function(it) {
				it.removeAttribute("href");
				it.removeAttribute("target");
				it.classList.remove("external");
				it.classList.remove("free");
			});
		}
	}
});

/**
 * ref http://youmightnotneedjquery.com/
 * @param {event} fn 
 */
function ready(fn) {
    if (document.readyState != 'loading'){
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
}