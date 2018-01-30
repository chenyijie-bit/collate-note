(function() {
    var newRem = function() {
        var html = document.documentElement;
        html.style.fontSize = html.getBoundingClientRect().width / 10 +'px'
    };
    window.addEventListener('resize', newRem, false);
    newRem();
})();

window.onload=function(){
    var slider = mui('.mui-slider');
    slider.slider({
        interval:3000 //自动轮播周期，若为0则不自动播放，默认为0；
    });
    //侧边栏
    $('.avatar').tap(function(){   
    	var zhezhao= $('.zhezhao')
     	var side=$('.side-left')
    		side.css('transform','translate3d(0,0,0)');
    		side.css('transition','all .6s');
    		zhezhao.show();
     	});
     
    $('.zhezhao').tap(function(){
      
       $('#side-left').css('transform','translate3d(-100%,0,0)');
       $('.zhezhao').css('display','none');
    });  
};