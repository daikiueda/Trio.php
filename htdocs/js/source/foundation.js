/*
  foundation.js
  サイト共通機能
*/

(function(){
	/**
	 * 設定項目
	 */

	// 本文領域の幅
	var WIDTH_CONTENT_BODY = 636;

	// ファーストビューの高さに関する調整の閾値
	var HEIGHT_FIRSTVIEW_THRESHOLD = 700;

	/* 並列カラムの高さあわせの適用クラス */
	var CLASS_PANEL_LOOK_ELMS = "div.unit-container";

	/* 並列カラムの見出しの高さあわせの適用クラス */
	var CLASS_TARGET_HEADING_ELMS = "div.heading";



	/**
	 * 定数定義
	 */

	// フォントサイズ変更UI
	var HTML_FONT_SIZE_SWITCH_UI =
		'<ol id="fontSizeSwitchUI">' +
		'  <li class="setterS"><a href="#">S</a></li>' +
		'  <li class="setterM"><a href="#">M</a></li>' +
		'  <li class="setterL"><a href="#">L</a></li>' +
		'</ol>';

	// ユーザー設定フォントサイズのcookie保管用キー名
	var STR_FONTSIZE_COOKIEKEY = 'fontsizeSetting';

	// ユーザー設定フォントサイズのcookie保管用キー名
	var STR_BANDWIDTH_COOKIEKEY = 'bandwidthSetting';

	// 各国サイトへのリンクの展開UIのクラス名
	var CLASS_COUNTRY_LINKS_TOGGLE = 'countrySpecificLinksTrigger';



	/**
	 * 汎用関数定義
	 */
	var atomjs = window.atomjs = {};

	/* クッキー操作 */
	atomjs.setCookie = function( myKey, myVal ){
		/* max-ageは、safari1系で不正という情報があるので、expiresDateで代替 */
		var expiresDateObj = new Date();
		if( arguments.length == 3 ){
			expiresDateObj = arguments[2];
		} else {
			expiresDateObj.setYear( expiresDateObj.getFullYear() + 1 );
		}
		var expiresDate = expiresDateObj.toGMTString().replace(/UTC/, "GTM");
		var tempStr = myKey + "=" + myVal + "; ";
		tempStr +="path=/;";
		tempStr +="expires=" + expiresDate + "; ";
		document.cookie = tempStr;
	}
	atomjs.getCookie = function( myKey ){
		var cookies = [];
		if( document.cookie ){
			var cookiesStr = document.cookie.split(";");
			for( var i=0; i < cookiesStr.length; i++ ){
				var cookiePair = cookiesStr[i].split("=");
				if( cookiePair.length != 2 ) continue;
				cookies[cookiePair[0].replace( /^ *| *$/g, "" )] = cookiePair[1].replace( /^ *| *$/g, "" );
			}
			return cookies[myKey];
		}
	}

	/* フォントサイズ変更の検地 */
	atomjs.fontSizeWatcher = function(){
		this.elm = $( '<div id="fontSizeWatcher">&nbsp;</div>' );
		$(document.body).append( this.elm );
		this.lastHeight = this.elm.height();
	}
	atomjs.fontSizeWatcher.prototype = {
		elm: null,
		lastHeight: 0,
		timer: null,
		test: function(){
			if( this.elm.height() !== this.lastHeight ){
				this.lastHeight = this.elm.height();
				this.elm.trigger("fontSizeChange");
			}
		},
		start: function(){
			var that = this;
			this.timer = setInterval( function(){that.test()}, 200 );
		},
		toString: function(){ return "atomjs.fontSizeWatcher" }
	}


	/* ページ内リンク */
	$.fn.inPageLink = function(){
		if( !$("html,body").animate ){
			return this;
		}

		return this.each( function(){
			if( !this.href.match(/#.+$/) ) return;

			$(this).click( function(){
				var targetId = this.href.match(/#.+$/)[0];

				var targetElm = $(targetId);
				if( targetElm.length !== 1 ) return true;
				if( targetElm.css("display") == "none" ) return false;

				var currentPos = $('body').scrollTop() || $('html').scrollTop();
				var targetPos = targetElm.offset().top;
				var targetDistance = Math.abs( currentPos - targetPos );

				var animateTime = targetDistance * 0.8;
				animateTime = Math.max( animateTime,  600 );
				animateTime = Math.min( animateTime, 1800 );

				$("html,body").animate({ scrollTop: targetPos }, animateTime, "swing", function(){
					location.href = location.href.replace(/(#.+|)$/,targetId);
				});
				return false;
			} );
		})
	}


	/* 並列カラムの高さあわせ */
	$.fn.adjustPanelHeight = function(){
		var targetPanels = this.find("> *");

		if(
			/* カラムが1つもない場合 */
			!targetPanels.get(0) ||

			/* もしくは、カラムが2つ以上ない場合 */
			!targetPanels.get(1) ||

			/* もしくは、2つのカラムの幅が異なる場合 */
			$(targetPanels.get(0)).width() !== $(targetPanels.get(1)).width()
		){
			/* 高さあわせを適用しない */
			return;
		}

		var turnCount = Math.floor(WIDTH_CONTENT_BODY / $(targetPanels.get(0)).width());

		var rowMaxHeight, headingMaxHeight, calcedValue, testElm;
		for( var i=0; i<targetPanels.length/turnCount; i++ ){
			rowMaxHeight = 0;
			headingMaxHeight = 0;

			for( var j=0; j<turnCount && i*turnCount+j < targetPanels.length; j++ ){
				testElm = $(CLASS_TARGET_HEADING_ELMS,targetPanels.get(i*turnCount+j));
				testElm.removeClass("adjusted");
				headingMaxHeight = Math.max( testElm.css("height","auto").height(), headingMaxHeight );
			}
			for( var j=0; j<turnCount && i*turnCount+j < targetPanels.length; j++ ){
				testElm = $(CLASS_TARGET_HEADING_ELMS,targetPanels.get(i*turnCount+j));
				testElm.height(headingMaxHeight).addClass("adjusted");
			}
			for( var j=0; j<turnCount && i*turnCount+j < targetPanels.length; j++ ){
				testElm = $(targetPanels.get(i*turnCount+j));
				rowMaxHeight = Math.max( testElm.css("height","auto").height(), rowMaxHeight );
			}
			for( var j=0; j<turnCount && i*turnCount+j < targetPanels.length; j++ ){
				testElm = $(targetPanels.get(i*turnCount+j));
				testElm.height(rowMaxHeight);
			}
		}
		return this;
	}

	/* 並列カラムの高さあわせ初期化 */
	$.fn.initPanelLinks = function(){
		return this.each(function(){
			var that = $(this);
			$("#fontSizeWatcher").bind("fontSizeChange",function(){that.adjustPanelHeight()});
			$(this).adjustPanelHeight();
		});
	}



	/* OL見栄え調整 */
	$.fn.dressOL = function(){
		return this.each( function(){
			$(this).addClass("dressed")
			.find("> li").wrapInner(document.createElement("div"))
			.find("> div").addClass("dressed-inner")
		})
	}



	/**
	 * 初期表示処理
	 */
	$(document).ready( function(){

		/* フォントサイズ変更の検出 */
		//var fSWatcher = new atomjs.fontSizeWatcher();
		//fSWatcher.start();


		/* ユーザー設定フォントサイズの適用 */
		/*
		switch( atomjs.getCookie(STR_FONTSIZE_COOKIEKEY) ){
			case "s":
				$(document.body).addClass("fontsizeS");
				break;
			case "l":
				$(document.body).addClass("fontsizeL");
				break;
		}
		*/


		/* フォントサイズ変更UIの初期化 */
		/*
		$("#contents-header").append( HTML_FONT_SIZE_SWITCH_UI );
		$("#contents-header .setterS a").click( function(){
			$(document.body)
				.removeClass("fontsizeL")
				.addClass("fontsizeS");
			atomjs.setCookie(STR_FONTSIZE_COOKIEKEY,"s");
			return false;
		} );
		$("#contents-header .setterM a").click( function(){
			$(document.body)
				.removeClass("fontsizeS")
				.removeClass("fontsizeL");
			atomjs.setCookie(STR_FONTSIZE_COOKIEKEY,"m");
			return false;
		} );
		$("#contents-header .setterL a").click( function(){
			$(document.body)
				.removeClass("fontsizeS")
				.addClass("fontsizeL");
			atomjs.setCookie(STR_FONTSIZE_COOKIEKEY,"l");
			return false;
		} );
		*/

		/* ページ内リンクの動作設定 */
		$("a[href]").inPageLink();

		/* OL.generalの粉飾 */
		$("ol.general").dressOL();

		/* 並列カラムの高さあわせ */
		//$( CLASS_PANEL_LOOK_ELMS ).initPanelLinks();

		/* ファーストビューの高さに関する調整 */
		//if( $(document.body).height() > HEIGHT_FIRSTVIEW_THRESHOLD ){
		//
		//	/* PageTopボタン表示 */
		//	$("#contents-footer .toPageTop").css("display","block")
		//}
		//else {
		//
		//	/* PageTopボタン非表示 */
		//	$("#contents-footer .toPageTop").css("display","none")
		//}

	});



	/**
	 * JS読み込み時処理
	 */

	/* CSSチラつき対応 */
	try {
		document.execCommand('BackgroundImageCache', false, true);
	} catch( myEx ) {}


})();
