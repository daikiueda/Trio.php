/**
 *
 */
(function(){
	var THIS_FILE_CONTEXT = "js/cliche.js";

	var required = [
		"source/rollover.js",
		"source/foundation.js"
	];

	(function(){
		var testElm, testElms = document.getElementsByTagName("script");
		for( var i=0; testElm = testElms[i]; i++ ){
			if( testElm.src.indexOf(THIS_FILE_CONTEXT) >= 0 ) break;
			testElm = null;
		}
		if( !testElm ) return;

		var pre_pathname = testElm.src.replace(/[^/\\]+$/,"");
		for( var i in required ){
			document.write('<script type="text/javascript" src="' + pre_pathname + required[i] + '"></script>');
		}
	})();
})();
