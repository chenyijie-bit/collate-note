
//实例了一个模块，专门来管理控制器
angular.module('Controllers',[])
.controller('DemoController',['$scope',function ($scope){
            console.log('启动了');
        }])
.controller('NavsController',['$scope',function ($scope){
        $scope.lists = [
            {url:'#/today',text:'今日一刻',icon:'icon-home'},
            {url:'#/older',text:'往期内容',icon:'icon-file-empty'},
            {url:'#/author',text:'热门作者',icon:'icon-pencil'},
            {url:'#/category',text:'栏目浏览',icon:'icon-menu'},
            {url:'#/favourite',text:'我的喜欢',icon:'icon-heart'},
            {url:'#/settings',text:'设置',icon:'icon-cog'}
        ]
    }])
    //今日一刻
.controller('TodayController',['$scope','$http','$filter','$rootScope',function ($scope,$http,$filter,$rootScope){
        $rootScope.title = '今日一刻';
        $rootScope.index = 0;
        $rootScope.loaded = false;

        var today = $filter('date')(new Date,'yyyy-MM-dd');

        $http({
            url:'./api/today.php',
            method:'get',
            params:{today:today}
        }).success(function (info){
            $rootScope.loaded = true;
            //console.log(info);
            $scope.today = info.date;
            $scope.posts = info.posts;
        })
    }])
    //往日内容
.controller('OlderController',['$scope','$http','$rootScope',function($scope,$http,$rootScope){
        $rootScope.title = '往日内容';
        $rootScope.index = 1;
        $rootScope.loaded = false;
        $http({
            url:'./api/older.php'
        }).success(function (info){
            //console.log(info);
            $rootScope.loaded = true;
            $scope.date = info.date;
            $scope.posts = info.posts;
        })
}])

    //热门作者
.controller('AuthorController',['$scope','$http','$rootScope',function ($scope,$http,$rootScope){
      $rootScope.index = 2;
      $rootScope.title = "热门作者";
      $rootScope.loaded = false;

       $http({
           url:'./api/author.php',
       }).success(function (info){
           $rootScope.loaded = true;
           console.log(info);
           $scope.all = info.all;
           $scope.rec = info.rec;

       })
}])
    //栏目浏览
    
    //我的喜欢
    //配置
