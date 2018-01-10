
    var App = angular.module('Yike',['ngRoute','Controllers','Directives']);

    //配置块加载内容
    App.config(['$routeProvider',function ($routeProvider){

        $routeProvider.when('/today',{
            templateUrl:'./views/today.html',
            controller:'TodayController'
        })
        .when('/older',{
              templateUrl:'./views/older.html' ,
              controller:'OlderController'
        })
        .when('/author',{
            templateUrl:'./views/author.html',
            controller:'AuthorController'
        })
        .otherwise({
             redireTo:'/today'
        })
    }]);

    //运行块,在根作用域下添加方法，可供全局使用
    App.run(['$rootScope',function ($rootScope){
        //设置类名的初始值
        $rootScope.collapsed = false;
        //全局方法
        $rootScope.toggle = function (){
            $rootScope.collapsed = ! $rootScope.collapsed;

            //获取所有的dd
            var navs = document.querySelectorAll('.navs dd');
            if($rootScope.collapsed){
                //打开
                //添加动画
                for(var i= 0,len=navs.length;i<len;i++){
                    navs[i].style.transform = 'translate(0)';
                    navs[i].style.transitionDelay = 0.2 +'s';
                    navs[i].style.transitionDuration = (i+1)*0.15 + 's';
                }
            }else{
                //关闭，倒序
                var len = navs.length;
                for(var j=len-1;j>0;j--){
                    navs[j].style.transform = 'translate(-100%)';
                    navs[j].style.transitionDelay = '';
                    navs[j].style.transitionDuration = (len-j)*0.15 + 's';
                }

            }
        }

    }]);




