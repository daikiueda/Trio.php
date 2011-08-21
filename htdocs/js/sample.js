/**
 * sample.js
 *
 * @author Daiki UEDA
 * @version 0.0.1
 */
(function(){
	var THIS_FILE_CONTEXT = "js/sample.js";

	/* Do not change the variable name */
	var required = [
		"source/sample_1.js",
		"source/sample_2.js",
		"source/sample_3.js"
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
