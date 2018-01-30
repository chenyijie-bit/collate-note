;(function(w,$){
    var loadmore = {

        get : function(callback, config){
            var config = config ? config : {}; /*防止未传参数报错*/

            var counter = 0; /*计数器*/
            var pageStart = 0;
            var pageSize = config.size ? config.size : 10;

            /*默认通过点击加载更多*/
            $(document).on('click', '.load-more', function(){
                counter ++;
                pageStart = counter * pageSize;
                callback && callback(config, pageStart, pageSize);

            });

            /*第一次自动加载*/
            callback && callback(config, pageStart, pageSize);
        },
    }

    $.loadmore = loadmore;
})(window, window.jQuery || window.Zepto);



