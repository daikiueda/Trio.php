/*
  rollover.js
  version 1.3
*/

var rollover = {};

rollover.offreg = /(.+)_off\.(png|jpg|gif)$/;
rollover.onreg = /(.+)_on\.(png|jpg|gif)$/;
//rollover.rolloverAnchorClassReg =  /rollover/;
rollover.rolloverAnchorClassReg =  /.*/; //BS暫定対応

rollover.preload = function() {
    var imgs = document.images;
    var loaded = {};
    var src = "";
    for ( var i=0; i<imgs.length; i++ ) {
        if ( rollover.offreg.test((src = imgs[i].src)) && !loaded[src] && (loaded[src] = true)) {
            (new Image()).src = imgs[i].src.replace(rollover.offreg,"$1_on.$2");
        }
    }
};

/*
  DOM生成タイミングでボタン画像の存在を確認
  参考:http://www.outofhanwell.com/blog/index.php?title=the_window_onload_problem_revisited
       http://dean.edwards.name/weblog/2006/06/again/
*/

// Mozilla
if (document.addEventListener) {
    document.addEventListener("DOMContentLoaded", rollover.preload, false);
}
// IE
/*@cc_on @*/
/*@if (@_win32)
document.write("<script id=__ie_onload defer><\/script>");
var script = document.getElementById("__ie_onload");
script.onreadystatechange = function() {
    if ( this.readyState === "complete" ) {
        rollover.preload();
    }
};
/*@end @*/

// Safari
if (/WebKit/i.test(navigator.userAgent)) {
    var _timer = setInterval(function() {
        if (/loaded|complete/.test(document.readyState)) {
            clearInterval(_timer);
            rollover.preload();
        }
    }, 10);
}

document.onmouseover = function(e) {
    var target = ( window.event ) ? window.event.srcElement : e.target;

    if ( target ) {
        if (
            (
                ( target.nodeType === 1 && target.tagName.toLowerCase() === "img" && rollover.offreg.test(target.src) ) &&
                target.parentNode.tagName.toLowerCase() === "a"
            ) ||
            /* BS暫定対応 */
            (
                target.nodeType === 1 && target.tagName.toLowerCase() === "span" && target.className.indexOf("language") !== -1
            )
        ) {
            var images = target.parentNode.getElementsByTagName("img");
            for ( var j=0; j<images.length; j++ ) {
                if ( rollover.offreg.test(images[j].src) ) {
                    images[j].src = images[j].src.replace(rollover.offreg,"$1_on.$2");
                }
            }
        }
        else if (
            ( target.nodeType === 1 && target.tagName.toLowerCase() === "a" && rollover.rolloverAnchorClassReg.test(target.className) ) ||
            ( target.nodeType === 3 && /WebKit/i.test(navigator.userAgent) && target.parentNode && target.parentNode.nodeType === 1 && target.parentNode.tagName.toLowerCase() === "a" && (target = target.parentNode) && rollover.rolloverAnchorClassReg.test(target.className) ) 
        ) {
            var images = target.getElementsByTagName("img");
            for ( var j=0; j<images.length; j++ ) {
                images[j].src = images[j].src.replace(rollover.offreg,"$1_on.$2");
            }
        }
    }
};

document.onmouseout = function(e) {
    var target = ( window.event ) ? window.event.srcElement : e.target;

    if ( target ) {
        if (
            (
                target.nodeType === 1 && target.tagName.toLowerCase() === "img" && rollover.onreg.test(target.src) &&
                target.parentNode.tagName.toLowerCase() === "a"
            ) ||
            /* BS暫定対応 */
            (
                target.nodeType === 1 && target.tagName.toLowerCase() === "span" && target.className.indexOf("language") !== -1
            )
        ) {
            var images = target.parentNode.getElementsByTagName("img");
            for ( var j=0; j<images.length; j++ ) {
                if ( rollover.onreg.test(images[j].src) ) {
                    images[j].src = images[j].src.replace(rollover.onreg,"$1_off.$2");
                }
            }
        }
        else if (
            ( target.nodeType === 1 && target.tagName.toLowerCase() === "a" && rollover.rolloverAnchorClassReg.test(target.className) ) ||
            ( target.nodeType === 3 && /WebKit/i.test(navigator.userAgent) && target.parentNode && target.parentNode.nodeType === 1 && target.parentNode.tagName.toLowerCase() === "a" && (target = target.parentNode) && rollover.rolloverAnchorClassReg.test(target.className) ) 
        ) {
            var images = target.getElementsByTagName("img");
            for ( var j=0; j<images.length; j++ ) {
                images[j].src = images[j].src.replace(rollover.onreg,"$1_off.$2");
            }
        }
    }
};
