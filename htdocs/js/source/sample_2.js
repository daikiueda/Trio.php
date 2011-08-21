/**
 * sample_2.js
 */
(function(){
	var defaultOnLoadFunction = window.onload;
	
	window.onload = function(){
		if( typeof defaultOnLoadFunction === 'function' ){
			defaultOnLoadFunction();
		}
		
		document.getElementById("JavaScript").innerHTML += '<p>&lsquo;/js/source/sample_2.js&rsquo; is done.</p>';	}
})();
