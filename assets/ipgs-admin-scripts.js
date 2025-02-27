/*!
 * jquery-timepicker v1.11.5 - A jQuery timepicker plugin inspired by Google Calendar. It supports both mouse and keyboard navigation.
 * Copyright (c) 2016 Jon Thornton - http://jonthornton.github.com/jquery-timepicker/
 * License: MIT
 */

!function(a){"object"==typeof exports&&exports&&"object"==typeof module&&module&&module.exports===exports?a(require("jquery")):"function"==typeof define&&define.amd?define(["jquery"],a):a(jQuery)}(function(a){function b(a){var b=a[0];return b.offsetWidth>0&&b.offsetHeight>0}function c(b){if(b.minTime&&(b.minTime=t(b.minTime)),b.maxTime&&(b.maxTime=t(b.maxTime)),b.durationTime&&"function"!=typeof b.durationTime&&(b.durationTime=t(b.durationTime)),"now"==b.scrollDefault)b.scrollDefault=function(){return b.roundingFunction(t(new Date),b)};else if(b.scrollDefault&&"function"!=typeof b.scrollDefault){var c=b.scrollDefault;b.scrollDefault=function(){return b.roundingFunction(t(c),b)}}else b.minTime&&(b.scrollDefault=function(){return b.roundingFunction(b.minTime,b)});if("string"===a.type(b.timeFormat)&&b.timeFormat.match(/[gh]/)&&(b._twelveHourTime=!0),b.showOnFocus===!1&&-1!=b.showOn.indexOf("focus")&&b.showOn.splice(b.showOn.indexOf("focus"),1),b.disableTimeRanges.length>0){for(var d in b.disableTimeRanges)b.disableTimeRanges[d]=[t(b.disableTimeRanges[d][0]),t(b.disableTimeRanges[d][1])];b.disableTimeRanges=b.disableTimeRanges.sort(function(a,b){return a[0]-b[0]});for(var d=b.disableTimeRanges.length-1;d>0;d--)b.disableTimeRanges[d][0]<=b.disableTimeRanges[d-1][1]&&(b.disableTimeRanges[d-1]=[Math.min(b.disableTimeRanges[d][0],b.disableTimeRanges[d-1][0]),Math.max(b.disableTimeRanges[d][1],b.disableTimeRanges[d-1][1])],b.disableTimeRanges.splice(d,1))}return b}function d(b){var c=b.data("timepicker-settings"),d=b.data("timepicker-list");if(d&&d.length&&(d.remove(),b.data("timepicker-list",!1)),c.useSelect){d=a("<select />",{"class":"ui-timepicker-select"});var g=d}else{d=a("<ul />",{"class":"ui-timepicker-list"});var g=a("<div />",{"class":"ui-timepicker-wrapper",tabindex:-1});g.css({display:"none",position:"absolute"}).append(d)}if(c.noneOption)if(c.noneOption===!0&&(c.noneOption=c.useSelect?"Time...":"None"),a.isArray(c.noneOption)){for(var i in c.noneOption)if(parseInt(i,10)==i){var k=e(c.noneOption[i],c.useSelect);d.append(k)}}else{var k=e(c.noneOption,c.useSelect);d.append(k)}if(c.className&&g.addClass(c.className),(null!==c.minTime||null!==c.durationTime)&&c.showDuration){"function"==typeof c.step?"function":c.step;g.addClass("ui-timepicker-with-duration"),g.addClass("ui-timepicker-step-"+c.step)}var l=c.minTime;"function"==typeof c.durationTime?l=t(c.durationTime()):null!==c.durationTime&&(l=c.durationTime);var n=null!==c.minTime?c.minTime:0,o=null!==c.maxTime?c.maxTime:n+u-1;n>o&&(o+=u),o===u-1&&"string"===a.type(c.timeFormat)&&c.show2400&&(o=u);var p=c.disableTimeRanges,v=0,x=p.length,y=c.step;"function"!=typeof y&&(y=function(){return c.step});for(var i=n,z=0;o>=i;z++,i+=60*y(z)){var A=i,B=s(A,c);if(c.useSelect){var C=a("<option />",{value:B});C.text(B)}else{var C=a("<li />");C.addClass(43200>A%86400?"ui-timepicker-am":"ui-timepicker-pm"),C.data("time",86400>=A?A:A%86400),C.text(B)}if((null!==c.minTime||null!==c.durationTime)&&c.showDuration){var D=r(i-l,c.step);if(c.useSelect)C.text(C.text()+" ("+D+")");else{var E=a("<span />",{"class":"ui-timepicker-duration"});E.text(" ("+D+")"),C.append(E)}}x>v&&(A>=p[v][1]&&(v+=1),p[v]&&A>=p[v][0]&&A<p[v][1]&&(c.useSelect?C.prop("disabled",!0):C.addClass("ui-timepicker-disabled"))),d.append(C)}if(g.data("timepicker-input",b),b.data("timepicker-list",g),c.useSelect)b.val()&&d.val(f(t(b.val()),c)),d.on("focus",function(){a(this).data("timepicker-input").trigger("showTimepicker")}),d.on("blur",function(){a(this).data("timepicker-input").trigger("hideTimepicker")}),d.on("change",function(){m(b,a(this).val(),"select")}),m(b,d.val(),"initial"),b.hide().after(d);else{var F=c.appendTo;"string"==typeof F?F=a(F):"function"==typeof F&&(F=F(b)),F.append(g),j(b,d),d.on("mousedown click","li",function(c){b.off("focus.timepicker"),b.on("focus.timepicker-ie-hack",function(){b.off("focus.timepicker-ie-hack"),b.on("focus.timepicker",w.show)}),h(b)||b[0].focus(),d.find("li").removeClass("ui-timepicker-selected"),a(this).addClass("ui-timepicker-selected"),q(b)&&(b.trigger("hideTimepicker"),d.on("mouseup.timepicker click.timepicker","li",function(a){d.off("mouseup.timepicker click.timepicker"),g.hide()}))})}}function e(b,c){var d,e,f;return"object"==typeof b?(d=b.label,e=b.className,f=b.value):"string"==typeof b?d=b:a.error("Invalid noneOption value"),c?a("<option />",{value:f,"class":e,text:d}):a("<li />",{"class":e,text:d}).data("time",String(f))}function f(a,b){return a=b.roundingFunction(a,b),null!==a?s(a,b):void 0}function g(b){if(b.target!=window){var c=a(b.target);c.closest(".ui-timepicker-input").length||c.closest(".ui-timepicker-wrapper").length||(w.hide(),a(document).unbind(".ui-timepicker"),a(window).unbind(".ui-timepicker"))}}function h(a){var b=a.data("timepicker-settings");return(window.navigator.msMaxTouchPoints||"ontouchstart"in document)&&b.disableTouchKeyboard}function i(b,c,d){if(!d&&0!==d)return!1;var e=b.data("timepicker-settings"),f=!1,d=e.roundingFunction(d,e);return c.find("li").each(function(b,c){var e=a(c);if("number"==typeof e.data("time"))return e.data("time")==d?(f=e,!1):void 0}),f}function j(a,b){b.find("li").removeClass("ui-timepicker-selected");var c=t(l(a),a.data("timepicker-settings"));if(null!==c){var d=i(a,b,c);if(d){var e=d.offset().top-b.offset().top;(e+d.outerHeight()>b.outerHeight()||0>e)&&b.scrollTop(b.scrollTop()+d.position().top-d.outerHeight()),d.addClass("ui-timepicker-selected")}}}function k(b,c){if(""!==this.value&&"timepicker"!=c){var d=a(this);if(!d.is(":focus")||b&&"change"==b.type){var e=d.data("timepicker-settings"),f=t(this.value,e);if(null===f)return void d.trigger("timeFormatError");var g=!1;if(null!==e.minTime&&null!==e.maxTime&&(f<e.minTime||f>e.maxTime)&&(g=!0),a.each(e.disableTimeRanges,function(){return f>=this[0]&&f<this[1]?(g=!0,!1):void 0}),e.forceRoundTime){var h=e.roundingFunction(f,e);h!=f&&(f=h,c=null)}var i=s(f,e);g?m(d,i,"error")&&d.trigger("timeRangeError"):m(d,i,c)}}}function l(a){return a.is("input")?a.val():a.data("ui-timepicker-value")}function m(a,b,c){if(a.is("input")){a.val(b);var d=a.data("timepicker-settings");d.useSelect&&"select"!=c&&"initial"!=c&&a.data("timepicker-list").val(f(t(b),d))}return a.data("ui-timepicker-value")!=b?(a.data("ui-timepicker-value",b),"select"==c?a.trigger("selectTime").trigger("changeTime").trigger("change","timepicker"):-1==["error","initial"].indexOf(c)&&a.trigger("changeTime"),!0):(a.trigger("selectTime"),!1)}function n(a){switch(a.keyCode){case 13:case 9:return;default:a.preventDefault()}}function o(c){var d=a(this),e=d.data("timepicker-list");if(!e||!b(e)){if(40!=c.keyCode)return!0;w.show.call(d.get(0)),e=d.data("timepicker-list"),h(d)||d.focus()}switch(c.keyCode){case 13:return q(d)&&(k.call(d.get(0),{type:"change"}),w.hide.apply(this)),c.preventDefault(),!1;case 38:var f=e.find(".ui-timepicker-selected");return f.length?f.is(":first-child")||(f.removeClass("ui-timepicker-selected"),f.prev().addClass("ui-timepicker-selected"),f.prev().position().top<f.outerHeight()&&e.scrollTop(e.scrollTop()-f.outerHeight())):(e.find("li").each(function(b,c){return a(c).position().top>0?(f=a(c),!1):void 0}),f.addClass("ui-timepicker-selected")),!1;case 40:return f=e.find(".ui-timepicker-selected"),0===f.length?(e.find("li").each(function(b,c){return a(c).position().top>0?(f=a(c),!1):void 0}),f.addClass("ui-timepicker-selected")):f.is(":last-child")||(f.removeClass("ui-timepicker-selected"),f.next().addClass("ui-timepicker-selected"),f.next().position().top+2*f.outerHeight()>e.outerHeight()&&e.scrollTop(e.scrollTop()+f.outerHeight())),!1;case 27:e.find("li").removeClass("ui-timepicker-selected"),w.hide();break;case 9:w.hide();break;default:return!0}}function p(c){var d=a(this),e=d.data("timepicker-list"),f=d.data("timepicker-settings");if(!e||!b(e)||f.disableTextInput)return!0;switch(c.keyCode){case 96:case 97:case 98:case 99:case 100:case 101:case 102:case 103:case 104:case 105:case 48:case 49:case 50:case 51:case 52:case 53:case 54:case 55:case 56:case 57:case 65:case 77:case 80:case 186:case 8:case 46:f.typeaheadHighlight?j(d,e):e.hide()}}function q(a){var b=a.data("timepicker-settings"),c=a.data("timepicker-list"),d=null,e=c.find(".ui-timepicker-selected");return e.hasClass("ui-timepicker-disabled")?!1:(e.length&&(d=e.data("time")),null!==d&&("string"!=typeof d&&(d=s(d,b)),m(a,d,"select")),!0)}function r(a,b){a=Math.abs(a);var c,d,e=Math.round(a/60),f=[];return 60>e?f=[e,v.mins]:(c=Math.floor(e/60),d=e%60,30==b&&30==d&&(c+=v.decimal+5),f.push(c),f.push(1==c?v.hr:v.hrs),30!=b&&d&&(f.push(d),f.push(v.mins))),f.join(" ")}function s(b,c){if("number"!=typeof b)return null;var d=parseInt(b%60),e=parseInt(b/60%60),f=parseInt(b/3600%24),g=new Date(1970,0,2,f,e,d,0);if(isNaN(g.getTime()))return null;if("function"===a.type(c.timeFormat))return c.timeFormat(g);for(var h,i,j="",k=0;k<c.timeFormat.length;k++)switch(i=c.timeFormat.charAt(k)){case"a":j+=g.getHours()>11?v.pm:v.am;break;case"A":j+=g.getHours()>11?v.PM:v.AM;break;case"g":h=g.getHours()%12,j+=0===h?"12":h;break;case"G":h=g.getHours(),b===u&&(h=c.show2400?24:0),j+=h;break;case"h":h=g.getHours()%12,0!==h&&10>h&&(h="0"+h),j+=0===h?"12":h;break;case"H":h=g.getHours(),b===u&&(h=c.show2400?24:0),j+=h>9?h:"0"+h;break;case"i":var e=g.getMinutes();j+=e>9?e:"0"+e;break;case"s":d=g.getSeconds(),j+=d>9?d:"0"+d;break;case"\\":k++,j+=c.timeFormat.charAt(k);break;default:j+=i}return j}function t(a,b){if(""===a||null===a)return null;if("object"==typeof a)return 3600*a.getHours()+60*a.getMinutes()+a.getSeconds();if("string"!=typeof a)return a;a=a.toLowerCase().replace(/[\s\.]/g,""),("a"==a.slice(-1)||"p"==a.slice(-1))&&(a+="m");var c="("+v.am.replace(".","")+"|"+v.pm.replace(".","")+"|"+v.AM.replace(".","")+"|"+v.PM.replace(".","")+")?",d=new RegExp("^"+c+"([0-9]?[0-9])\\W?([0-5][0-9])?\\W?([0-5][0-9])?"+c+"$"),e=a.match(d);if(!e)return null;var f=parseInt(1*e[2],10);if(f>24){if(b&&b.wrapHours===!1)return null;f%=24}var g=e[1]||e[5],h=f;if(12>=f&&g){var i=g==v.pm||g==v.PM;h=12==f?i?12:0:f+(i?12:0)}var j=1*e[3]||0,k=1*e[4]||0,l=3600*h+60*j+k;if(12>f&&!g&&b&&b._twelveHourTime&&b.scrollDefault){var m=l-b.scrollDefault();0>m&&m>=u/-2&&(l=(l+u/2)%u)}return l}var u=86400,v={am:"am",pm:"pm",AM:"AM",PM:"PM",decimal:".",mins:"mins",hr:"hr",hrs:"hrs"},w={init:function(b){return this.each(function(){var e=a(this),f=[];for(var g in a.fn.timepicker.defaults)e.data(g)&&(f[g]=e.data(g));var h=a.extend({},a.fn.timepicker.defaults,f,b);if(h.lang&&(v=a.extend(v,h.lang)),h=c(h),e.data("timepicker-settings",h),e.addClass("ui-timepicker-input"),h.useSelect)d(e);else{if(e.prop("autocomplete","off"),h.showOn)for(var i in h.showOn)e.on(h.showOn[i]+".timepicker",w.show);e.on("change.timepicker",k),e.on("keydown.timepicker",o),e.on("keyup.timepicker",p),h.disableTextInput&&e.on("keydown.timepicker",n),k.call(e.get(0),null,"initial")}})},show:function(c){var e=a(this),f=e.data("timepicker-settings");if(c&&c.preventDefault(),f.useSelect)return void e.data("timepicker-list").focus();h(e)&&e.blur();var k=e.data("timepicker-list");if(!e.prop("readonly")&&(k&&0!==k.length&&"function"!=typeof f.durationTime||(d(e),k=e.data("timepicker-list")),!b(k))){e.data("ui-timepicker-value",e.val()),j(e,k),w.hide(),k.show();var m={};f.orientation.match(/r/)?m.left=e.offset().left+e.outerWidth()-k.outerWidth()+parseInt(k.css("marginLeft").replace("px",""),10):m.left=e.offset().left+parseInt(k.css("marginLeft").replace("px",""),10);var n;n=f.orientation.match(/t/)?"t":f.orientation.match(/b/)?"b":e.offset().top+e.outerHeight(!0)+k.outerHeight()>a(window).height()+a(window).scrollTop()?"t":"b","t"==n?(k.addClass("ui-timepicker-positioned-top"),m.top=e.offset().top-k.outerHeight()+parseInt(k.css("marginTop").replace("px",""),10)):(k.removeClass("ui-timepicker-positioned-top"),m.top=e.offset().top+e.outerHeight()+parseInt(k.css("marginTop").replace("px",""),10)),k.offset(m);var o=k.find(".ui-timepicker-selected");if(!o.length){var p=t(l(e));null!==p?o=i(e,k,p):f.scrollDefault&&(o=i(e,k,f.scrollDefault()))}if(o&&o.length){var q=k.scrollTop()+o.position().top-o.outerHeight();k.scrollTop(q)}else k.scrollTop(0);return f.stopScrollPropagation&&a(document).on("wheel.ui-timepicker",".ui-timepicker-wrapper",function(b){b.preventDefault();var c=a(this).scrollTop();a(this).scrollTop(c+b.originalEvent.deltaY)}),a(document).on("touchstart.ui-timepicker mousedown.ui-timepicker",g),a(window).on("resize.ui-timepicker",g),f.closeOnWindowScroll&&a(document).on("scroll.ui-timepicker",g),e.trigger("showTimepicker"),this}},hide:function(c){var d=a(this),e=d.data("timepicker-settings");return e&&e.useSelect&&d.blur(),a(".ui-timepicker-wrapper").each(function(){var c=a(this);if(b(c)){var d=c.data("timepicker-input"),e=d.data("timepicker-settings");e&&e.selectOnBlur&&q(d),c.hide(),d.trigger("hideTimepicker")}}),this},option:function(b,e){return"string"==typeof b&&"undefined"==typeof e?a(this).data("timepicker-settings")[b]:this.each(function(){var f=a(this),g=f.data("timepicker-settings"),h=f.data("timepicker-list");"object"==typeof b?g=a.extend(g,b):"string"==typeof b&&(g[b]=e),g=c(g),f.data("timepicker-settings",g),h&&(h.remove(),f.data("timepicker-list",!1)),g.useSelect&&d(f)})},getSecondsFromMidnight:function(){return t(l(this))},getTime:function(a){var b=this,c=l(b);if(!c)return null;var d=t(c);if(null===d)return null;a||(a=new Date);var e=new Date(a);return e.setHours(d/3600),e.setMinutes(d%3600/60),e.setSeconds(d%60),e.setMilliseconds(0),e},isVisible:function(){var a=this,c=a.data("timepicker-list");return!(!c||!b(c))},setTime:function(a){var b=this,c=b.data("timepicker-settings");if(c.forceRoundTime)var d=f(t(a),c);else var d=s(t(a),c);return a&&null===d&&c.noneOption&&(d=a),m(b,d),b.data("timepicker-list")&&j(b,b.data("timepicker-list")),this},remove:function(){var a=this;if(a.hasClass("ui-timepicker-input")){var b=a.data("timepicker-settings");return a.removeAttr("autocomplete","off"),a.removeClass("ui-timepicker-input"),a.removeData("timepicker-settings"),a.off(".timepicker"),a.data("timepicker-list")&&a.data("timepicker-list").remove(),b.useSelect&&a.show(),a.removeData("timepicker-list"),this}}};a.fn.timepicker=function(b){return this.length?w[b]?this.hasClass("ui-timepicker-input")?w[b].apply(this,Array.prototype.slice.call(arguments,1)):this:"object"!=typeof b&&b?void a.error("Method "+b+" does not exist on jQuery.timepicker"):w.init.apply(this,arguments):this},a.fn.timepicker.defaults={appendTo:"body",className:null,closeOnWindowScroll:!1,disableTextInput:!1,disableTimeRanges:[],disableTouchKeyboard:!1,durationTime:null,forceRoundTime:!1,maxTime:null,minTime:null,noneOption:!1,orientation:"l",roundingFunction:function(a,b){if(null===a)return null;if("number"!=typeof b.step)return a;var c=a%(60*b.step);return c>=30*b.step?a+=60*b.step-c:a-=c,a==u&&b.show2400?a:a%u},scrollDefault:null,selectOnBlur:!1,show2400:!1,showDuration:!1,showOn:["click","focus"],showOnFocus:!0,step:30,stopScrollPropagation:!1,timeFormat:"g:ia",typeaheadHighlight:!0,useSelect:!1,wrapHours:!0}});

jQuery(document).ready(function($) {

    function ipgsListenerInit($this) {
        $this.find('.ipgs-js-hide').hide();
        // tooltips
        $this.find('.ipgs-tooltip-link').click( function(){
            $(this).siblings('.ipgs-tooltip').slideToggle();
        });
        // date picker
        var nowTime = Date.now();

        $this.find('.ipgs-date-picker').each(function() {
            $(this).datepicker({
                defaultDate: nowTime,
                dateFormat: 'yy-mm-dd',
                minDate : 0
                /*
                beforeShow: function( element, object ){
                    // Capture the datepicker div here; it's dynamically generated so best to grab here instead of elsewhere.
                    //$dpDiv = $( object.dpDiv );
                    // "Namespace" our CSS a bit so that our custom jquery-ui-datepicker styles don't interfere with other plugins'/themes'.
                    $dpDiv.addClass( 'ui-datepicker' );
                }*/
            });
        });

        // time picker
        if (typeof $().timepicker !== 'undefined') {
            $this.find('.ipgs-time-picker').each(function() {
                $(this).timepicker();
            });
        }

        $this.find('.ipgs-limit-posts-checkbox').each(function() {
            if ($(this).is(':checked')) {
                $(this).closest('.ipgs-job-setting-wrap').find('.ipgs-num-copy-wrap').show();
            } else {
                $(this).closest('.ipgs-job-setting-wrap').find('.ipgs-num-copy-wrap').hide();
            }
        });

        $this.find('.ipgs-delete-job-tool').click(function(event) {
            event.preventDefault();
            if (confirm(ipgsAdminScript.confirmEnd)) {
                var $self = $(this);
                ipgsChanging($self.closest('.ipgs-single-job'));
                var submitData = {
                        'action': 'ipgs_ajax',
                        'context': 'delete',
                        'job_id' : $self.closest('.ipgs-single-job').find('.ipgs-job_id-input').val()
                    },
                    successFunc = function(data) {
                        if (data.indexOf('<div') > -1) {
                            $self.closest('.ipgs-single-job').replaceWith(data);
                        } else if (data.indexOf('{') === 0) {
                            var theResponse = JSON.parse(data);
                            $self.closest('.ipgs-job-wrap').find('.ipgs-alert.ipgs-error').remove();
                            $self.closest('.ipgs-job-wrap').prepend('<div class="ipgs-alert ipgs-error"><p>'+theResponse.error+'</p><p>'+theResponse.error_message+'</p></div>');
                        }
                    };
                ipgsAjax(submitData,successFunc,true)
            }
        });

        $this.find('.ipgs-job-settings-tool, #ipgs-nav-schedule').click(function(event) {
            event.preventDefault();
            var $self = $(this);
            ipgsChanging($self.closest('.ipgs-single-job'));
            var submitData = {
                    'action': 'ipgs_ajax',
                    'context': 'job_settings',
                    'job_id' : $self.closest('.ipgs-single-job').find('.ipgs-job_id-input').val()
                },
                successFunc = function(data) {
                    if (data.indexOf('<div') > -1) {
                        $self.closest('.ipgs-single-job').replaceWith(data);
                        if(!$self.closest('.ipgs-job-wrap').find('.ipgs-post-search').length) {
                            $('html, body').animate({
                                scrollTop: $('.ipgs-new').offset().top - 100
                            }, 750);
                        }
                        ipgsListenerInit($('.ipgs-new'));
                        $('.ipgs-new').removeClass('ipgs-new');
                    } else if (data.indexOf('{') === 0) {
                        var theResponse = JSON.parse(data);
                        $self.closest('.ipgs-job-wrap').find('.ipgs-alert.ipgs-error').remove();
                        $self.closest('.ipgs-job-wrap').prepend('<div class="ipgs-alert ipgs-error"><p>'+theResponse.error+'</p><p>'+theResponse.error_message+'</p></div>');
                    }
                    ipgsDoneChanging($self.closest('.ipgs-job-wrap'));
                };
            ipgsAjax(submitData,successFunc,true)
        });
        //single_job
        $this.find('#ipgs-nav-post, #ipgs-nav-post-next, #ipgs-nav-post-prev').click(function(event) {
            event.preventDefault();
            var $self = $(this),
                page = typeof $self.attr('data-page') !== 'undefined' ? $self.attr('data-page') : 1;
            ipgsChanging($self.closest('.ipgs-single-job'));
            var submitData = {
                    'action': 'ipgs_ajax',
                    'context': 'single_job',
                    'page': page,
                    'job_id': $self.closest('.ipgs-single-job').find('.ipgs-job_id-input').val()
                },
                successFunc = function(data) {
                    if (data.indexOf('<div') > -1) {
                        $self.closest('.ipgs-single-job').replaceWith(data);
                        if(!$self.closest('.ipgs-job-wrap').find('.ipgs-post-search').length) {
                            $('html, body').animate({
                                scrollTop: $('.ipgs-new').offset().top - 100
                            }, 750);
                        }
                        ipgsListenerInit($('.ipgs-new'));
                        $('.ipgs-new').removeClass('ipgs-new');
                    } else if (data.indexOf('{') === 0) {
                        var theResponse = JSON.parse(data);
                        $self.closest('.ipgs-job-wrap').find('.ipgs-alert.ipgs-error').remove();
                        $self.closest('.ipgs-job-wrap').prepend('<div class="ipgs-alert ipgs-error"><p>'+theResponse.error+'</p><p>'+theResponse.error_message+'</p></div>');
                    }
                    ipgsDoneChanging($self.closest('.ipgs-job-wrap'));
                };
            ipgsAjax(submitData,successFunc,true)
        });

        $this.find('input[type=submit], .ipgs-post-search-go').click(function(event) {
            event.preventDefault();
            var $self = $(this);
            ipgsChanging($self.closest('.ipgs-job-wrap'));
            //var formData = $self.closest('form').serializeArray()
            var submitData = {};
            if ($self.closest('.ipgs-job-wrap').find('.ipgs-blog_url-input').length) {
                submitData = {
                    'action' : 'ipgs_ajax_blog_url',
                    'blog_url' : $self.closest('.ipgs-job-wrap').find('.ipgs-blog_url-input').val(),
                    'job_id' : $self.closest('.ipgs-job-wrap').find('.ipgs-job_id-input').val()
                };
            } else if ($self.closest('.ipgs-job-wrap').find('.ipgs-category-input').length) {
                submitData = {
                    'action': 'ipgs_ajax_step_two',
                    'job_id': $self.closest('.ipgs-single-job').find('.ipgs-job_id-input').val(),
                    'category': $self.closest('.ipgs-single-job').find('.ipgs-category-input').val(),
                    'limit_posts': $self.closest('.ipgs-single-job').find('.ipgs-limit-posts-checkbox').is(':checked'),
                    'max_posts': $self.closest('.ipgs-single-job').find('.ipgs-max_posts-input').val(),
                    'interval_val': $self.closest('.ipgs-single-job').find('.ipgs-num-input').val(),
                    'interval_unit': $self.closest('.ipgs-single-job').find('.ipgs-interval_unit-input').val(),
                    'start_date': $self.closest('.ipgs-single-job').find('.ipgs-date-picker').val(),
                    'start_time': $self.closest('.ipgs-single-job').find('.ipgs-time-picker').val(),
                    'post_status': $self.closest('.ipgs-single-job').find('.ipgs-post_status-input').val()
                };
            } else if ($self.closest('.ipgs-job-wrap').find('.ipgs-post-search').length) {
                var selected_ids = [];
                $self.closest('.ipgs-job-wrap').find('.ipgs-post-selection-wrap').each(function() {
                    if ($(this).find('input').is(':checked')) {
                        selected_ids.push($(this).find('input').val());
                    }
                });
                if (selected_ids.length === 0 && !($self.hasClass('ipgs-post-search-go'))) {
                    ipgsDoneChanging($self.closest('.ipgs-job-wrap'));
                    $self.closest('.ipgs-job-wrap').find('.ipgs-alert.ipgs-error').remove();
                    $self.closest('.ipgs-job-wrap').append('<div class="ipgs-alert ipgs-error"><p>No posts selected</p></div>');
                    return;
                }
                submitData = {
                    'action': 'ipgs_ajax_process_selected_posts',
                    'job_id': $self.closest('.ipgs-single-job').find('.ipgs-job_id-input').val(),
                    'selected_ids': selected_ids,
                    'search': $self.closest('.ipgs-single-job').find('.ipgs-post-search').val(),
                    'is_search' : ($self.hasClass('ipgs-post-search-go')),
                    'from_search' : $self.find('.ipgs-is-search').length
                };
            }
            successFunc = function(data) {
                if (data.indexOf('<div') > -1) {
                    $self.closest('.ipgs-single-job').replaceWith(data);
                    if(!$self.closest('.ipgs-job-wrap').find('.ipgs-post-search').length) {
                        $('html, body').animate({
                            scrollTop: $('.ipgs-new').offset().top - 100
                        }, 750);
                    }
                    ipgsListenerInit($('.ipgs-new'));
                    $('.ipgs-new').removeClass('ipgs-new');
                } else if (data.indexOf('{') === 0) {
                    var theResponse = JSON.parse(data);
                    $self.closest('.ipgs-job-wrap').find('.ipgs-alert.ipgs-error').remove();
                    $self.closest('.ipgs-job-wrap').prepend('<div class="ipgs-alert ipgs-error"><p>'+theResponse.error+'</p><p>'+theResponse.error_message+'</p></div>');
                }
                ipgsDoneChanging($self.closest('.ipgs-job-wrap'));
            };
            ipgsAjax(submitData,successFunc,true)
        });
    }

    $('.ipgs-single-job').each(function() {
        ipgsListenerInit($(this));
    });

    $('#ipgs-add-new-job').click(function(event) {
        event.preventDefault();
        var $self = $(this);
        ipgsChanging($self);

        var submitData = {
            'action' : 'ipgs_ajax_new_job'
        },
        successFunc = function(data) {
            if (data.indexOf('<div') > -1) {
                $self.before(data);
                ipgsListenerInit($('.ipgs-new'));
                $('.ipgs-new').removeClass('ipgs-new');
            } else if (data.indexOf('{') === 0) {
                var theResponse = JSON.parse(data);
                $self.closest('.ipgs-job-wrap').find('.ipgs-alert.ipgs-error').remove();
                $self.closest('.ipgs-job-wrap').prepend('<div class="ipgs-alert ipgs-error"><p>'+theResponse.error+'</p><p>'+theResponse.error_message+'</p></div>');
            }
            ipgsDoneChanging($self);
        };
        ipgsAjax(submitData,successFunc,true)
    });

    var $body = $('body');
    $body.on('click', '.ipgs-limit-posts-checkbox', function (event) {
        if ($(event.target).is(':checked')) {
            $(event.target).closest('.ipgs-job-setting-wrap').find('.ipgs-num-copy-wrap').show();
        } else {
            $(event.target).closest('.ipgs-job-setting-wrap').find('.ipgs-num-copy-wrap').hide();
        }
    });


    function ipgsChanging($nearest) {
        $nearest.addClass('ipgs-changing').fadeTo(500,.5);
    }

    function ipgsDoneChanging($nearest) {
        $nearest.removeClass('ipgs-changing').fadeTo(500,1);
    }

    function ipgsAjax(submitData,successFunc,nonce) {
        if (nonce) {
            submitData.ipgs_nonce = ipgsAdminScript.ipgs_nonce;
        }
        $.ajax({
            url: ipgsAdminScript.ajaxUrl,
            type: 'post',
            data: submitData,
            success: successFunc
        });
    }

});