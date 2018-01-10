
//   Created by kk on 16/3/28.
//  /
// /
//     hotcss 可能有神奇的问题

!function() {
    var innerStyle = "@charset \"utf-8\";html{color:#000;background:#fff;overflow-y:scroll;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}html {outline:0;-webkit-text-size-adjust:none;-webkit-tap-highlight-color:rgba(0,0,0,0)}html,body{font-family:sans-serif}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td,hr,button,article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{margin:0;padding:0}input,select,textarea{font-size:100%}table{border-collapse:collapse;border-spacing:0}fieldset,img{border:0}abbr,acronym{border:0;font-variant:normal}del{text-decoration:line-through}address,caption,cite,code,dfn,em,th,var{font-style:normal;font-weight:500}ol,ul{list-style:none}caption,th{text-align:left}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:500}q:before,q:after{content:''}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-.5em}sub{bottom:-.25em}a:hover{text-decoration:underline}ins,a{text-decoration:none}",
        createStyle = document.createElement("style");
    if (document.getElementsByTagName("head")[0].appendChild(createStyle),
            createStyle.styleSheet)
        createStyle.styleSheet.disabled || (createStyle.styleSheet.cssText = innerStyle);
    else
        try {
            createStyle.innerHTML = innerStyle;
        } catch (ex) {
            createStyle.innerText = innerStyle;
        }
}();
! function(window, nameSpace) {
    var timer, doc = window.document,
        docEl = doc.documentElement,
        metaEl = doc.querySelector('meta[name="viewport"]'),
        flexibleEl = doc.querySelector('meta[name="flexible"]'),
        dpr = 0,
        scale = 0,
        Flexible = nameSpace.flexible || (nameSpace.flexible = {});
        // 给Flexible 开创命名空间
    //刷新rem
    function refreshRem() {
        var width = docEl.getBoundingClientRect().width;
        //width / dpr > 540 && (width = 540  dpr);
        if(width / dpr > 540) {
            width = 540  ;
        }
        var rootSize = width / 10;
        docEl.style.fontSize = rootSize + "px",
         Flexible.rem = window.rem = rootSize;
    }
    if (metaEl) {
       console.warn('将根据已有的meta标签来设置缩放比例');
       var match = metaEl.getAttribute('content').match(/initial-scale=([\d.]+)/);
       if (match) {
           scale = parseFloat(match[1]);
           dpr = parseInt(1 / scale);
       }
       //如果在meta标签中，我们手动配置了flexible，则使用里面的内容
    } else if (flexibleEl) {
       var content = flexibleEl.getAttribute('content');
       if (content) {
           var initialDpr = content.match(/initial-dpr=([\d.]+)/);
           var maximumDpr = content.match(/maximum-dpr=([\d.]+)/);
           if (initialDpr) {
               dpr = parseFloat(initialDpr[1]);
               scale = parseFloat((1 / dpr).toFixed(2));
           }
           if (maximumDpr) {
               dpr = parseFloat(maximumDpr[1]);
               scale = parseFloat((1 / dpr).toFixed(2));
           }
       }
    }
    if (!dpr && !scale) {
        var isAndroid = window.navigator.appVersion.match(/android/gi);
        var isIPhone = window.navigator.appVersion.match(/iphone/gi);
        //devicePixelRatio这个属性是可以获取到设备的dpr的
        var devicePixelRatio = window.devicePixelRatio;
        if (isIPhone) {
            if (devicePixelRatio >= 3 && (!dpr || dpr >= 3)) {
                dpr = 3;
            } else if (devicePixelRatio >= 2 && (!dpr || dpr >= 2)){
                dpr = 2;
            } else {
                dpr = 1;
            }
        } else {
            dpr = 1;
        }
        scale = 1 / dpr;
    }
    if (docEl.setAttribute("data-dpr", dpr), !metaEl)
        if (metaEl = doc.createElement("meta"),
                metaEl.setAttribute("name", "viewport"),            //j = scale            //j = scale              //j = scale
                metaEl.setAttribute("content", "initial-scale=" + scale + ", maximum-scale=" + scale + ", minimum-scale=" + scale + ", user-scalable=no"),
                docEl.firstElementChild)
            docEl.firstElementChild.appendChild(metaEl);
        else {
            var createDiv = doc.createElement("div");
            createDiv.appendChild(metaEl),
                doc.write(createDiv.innerHTML)
            }
    window.addEventListener("resize", function() {
                clearTimeout(timer),
                    timer = setTimeout(refreshRem, 300);
        }, !1),
            window.addEventListener("pageshow", function(a) {
            a.persisted && (clearTimeout(timer),
                timer = setTimeout(refreshRem, 300))
        }, !1),
        "complete" === doc.readyState ? doc.body.style.fontSize = 17  dpr + "px" : doc.addEventListener("DOMContentLoaded", function() {
            doc.body.style.fontSize = 17  dpr + "px"
        }, !1),
        refreshRem(),
        Flexible.dpr = window.dpr = dpr,
        Flexible.refreshRem = refreshRem,
        Flexible.rem2px = function(a) {
            var pxValue = parseFloat(a)  this.rem;
            return "string" == typeof a && a.match(/rem$/) && (pxValue += "px"), pxValue ;
        },
        Flexible.px2rem = function(a) {
            var remValue = parseFloat(a) / this.rem;
            return "string" == typeof a && a.match(/px$/) && (remValue += "rem"), remValue ;
        }
}(window, window.lib || (window.lib = {}));