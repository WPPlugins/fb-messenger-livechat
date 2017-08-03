jQuery(document).ready(function($) {

    function getCookie(c) {
        return (c=(document.cookie.match('(^|; )'+ c +'=([^;]*)')||0)[2]) && decodeURIComponent(c);
    }

    function setCookie(name, value, days) {
        var cookie = name + '=' + encodeURIComponent(value);
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            cookie += ';expires=' + date.toGMTString();
        }
        cookie += ';path=/';
        document.cookie = cookie;
    }

    var $sheet = $('.wp-sheet');

    function toggleSheet() {
        if ($sheet.is(':visible')) {
            $sheet.hide();
            setCookie('fbmsg-sheet-open', '0', 365);
        } else {
            $sheet.show();
            sheetResize();
            setCookie('fbmsg-sheet-open', '1', 365);
        }
    }

    var $sheetContent = $('.wp-sheet-content'),
        $sheetContentPart = $('.wp-sheet-content-part');

    function sheetResize() {
        var $sheetContentInner = $('.wp-sheet-content-inner'),
            width = $sheetContentInner.width(),
            height = $sheetContentInner.height();

        $sheetContentPart.html(
          '<div class="fb-page" data-tabs="messages' + (settingsVars.timeline && ', timeline') + (settingsVars.events && ', events') +'" data-href="' + settingsVars.page + '" data-width="' + width + '" data-height="' + height + '" data-href="' + settingsVars.page + '" data-small-header="true"  data-hide-cover="false" data-show-facepile="true" data-adapt-container-width="false">' +
		    '<div class="fb-xfbml-parse-ignore">' +
			  '<blockquote>Loading...</blockquote>' +
		    '</div>' +
	      '</div>');
        if ('FB' in window) {
            FB.XFBML.parse();
        }
    }

    $('.fbmsg-badge').click(function() {
        toggleSheet();
        return false;
    });

    $('.wp-sheet-head-close').click(function() {
        toggleSheet();
        return false;
    });

    if (getCookie('fbmsg-sheet-open') == '1') {
        $sheet.show();
        sheetResize();
    } else {
        $sheet.hide();
    }

    var waitForFinalEvent = (function () {
      var timers = {};
      return function (callback, ms, uniqueId) {
        if (!uniqueId) {
          uniqueId = "Don't call this twice without a uniqueId";
        }
        if (timers[uniqueId]) {
          clearTimeout (timers[uniqueId]);
        }
        timers[uniqueId] = setTimeout(callback, ms);
      };
    })();

    $(window).resize(function() {
        $sheetContentPart.html('<div class="wp-spin"></div>');
        waitForFinalEvent(function(){
            sheetResize();
        }, 500, "some unique string");
    });
});