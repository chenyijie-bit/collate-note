// 字数控制
    function ziShu(selecter, num) {
        var selecter=$(selecter);
        var selecterval=selecter.text();
        // alert(selecterval);
        var len = selecterval.length;
        if (len > num) {
            $(selecter).html(selecterval.split("").slice(0, 38).join("") + "...");
        }
    }
  


$(function () {
    var div2 = $("#div2");
    var div1 = $("#div1");
    div2.click(function () {
        if (div1.hasClass("close1")) {
            div1.removeClass("close1").addClass("open1")
        } else {
            div1.addClass("close1")
        }

        if (div2.hasClass("close2")) {
            div2.removeClass("close2").addClass("open2")
        } else {
            div2.addClass("close2")
        }
        // div1.className = (div1.className == "close1") ? "open1" : "close1";
        // div2.className = (div2.className == "close2") ? "open2" : "close2";
    });

});
$(function () {
    //点击弹出时间选择器
    $(".doc-time").click(function () {
        if ($("#interdict1").hasClass("hide")) {
            $("#interdict1").removeClass("hide").addClass("show")
        }
    });
    $("#time-btn2").click(function () {
        if ($("#interdict1").hasClass("show")) {
            $("#interdict1").removeClass("show").addClass("hide")
        }
    });
    $("#time-btn1").onclick = function () {
        var str;
//            alert("事件触发");
//            time.className = ( time.className == "hide") ? "show" : "hide";
        str = $(".time-content").child("span")[0].innerHTML;
        console.log(str);
    }
    $("#chuzhen").click(function () {
//        alert(1);
        if ($("#fuzhen").hasClass("select-currunt")) {
            $("#fuzhen").removeClass("select-currunt");
            $("#chuzhen").addClass("select-currunt");
            return 1;

        }
        else if ($("#chuzhen").hasClass("select-currunt")) {
            $("#chuzhen").removeClass("select-currunt")
        } else {
            $("#chuzhen").addClass("select-currunt");
            return 1;
        }


    })
    $("#fuzhen").click(function () {
//        alert(1);
        if ($("#chuzhen").hasClass("select-currunt")) {
            $("#chuzhen").removeClass("select-currunt");
            $("#fuzhen").addClass("select-currunt");
            return 2;
        }

        else if ($("#fuzhen").hasClass("select-currunt")) {
            $("#fuzhen").removeClass("select-currunt");
        } else {
            $("#fuzhen").addClass("select-currunt");

        }


    });
    //点击添加弹出框显示
    $("#add").click(function () {
        if ($("#interdict2").hasClass("hide")) {
            $("#interdict2").removeClass("hide").addClass("show")
        }
    });
    //点击编辑弹出框显示
    $("#edit").click(function () {
        if ($("#interdict3").hasClass("hide")) {
            $("#interdict3").removeClass("hide").addClass("show")
        }
    })

    $("#pon-btn").click(function () {
        if ($("#interdict3").hasClass("show")) {
            $("#interdict3").removeClass("show").addClass("hide");
            $("#interdict2").removeClass("hide").addClass("show");
        }
    });
    $(".pon-name").click(function () {
        if ($("#interdict3").hasClass("show")) {
            $("#interdict3").removeClass("show").addClass("hide");
            // $("#interdict2").removeClass("hide").addClass("show");
        }
    })
})
//菜单栏样式切换
$(function () {
    $(".tab-group").click(function () {
        $(this.children[0].children[0]).css("color", "#007aff");
        $($(".tab-personal").children().children()[0]).css("color", "");
        $($(".tab-home").children().children()[0]).css("color", "");
        $(".tab-group").addClass("group-currunt");

        $(".tab-home").removeClass("home-currunt");
        $(".tab-personal").removeClass("personal-currunt");

    });
    $(".tab-home").click(function () {
        $(this.children[0].children[0]).css("color", "#007aff");
//
        $(".tab-home").addClass("home-currunt");
        $($(".tab-personal").children().children()[0]).css("color", "");
        $($(".tab-group").children().children()[0]).css("color", "");
        $(".tab-personal").removeClass("personal-currunt");
        $(".tab-group").removeClass("group-currunt");

    })
    $(".tab-personal").click(function () {
        $(this.children[0].children[0]).css("color", "#007aff");
        $($(".tab-home").children().children()[0]).css("color", "");
        $($(".tab-group").children().children()[0]).css("color", "");

//
        $(".tab-personal").addClass("personal-currunt");
        $(".tab-home").removeClass("home-currunt");
        $(".tab-group").removeClass("group-currunt");


    })
});
$(function () {
    function active(selecter, str) {
//        var collect=document.getElementById("collect");
        $(selecter).click(function () {
            if ($(selecter).hasClass(str)) {
                alert("您已关注");
            } else {
                $(selecter).addClass(str);
            }
        })


    }

    active("#collect", "select");
    active("#good", "good")
})

$(function () {
    // doctor-free 页面上传图片
    // 点击头像传图片
    function fileToBase64(file, callback, outputFormat) {
        var file = file[0];
        if (file.files && file.files[0]) {
            var reader = new FileReader();
            reader.onload = function (evt) {
                callback.call(this, evt.target.result);
            }
            reader.readAsDataURL(file.files[0]);
        }
    }

    $("#freefileT").on('change', function () {
        fileToBase64($(this), function (imgBase64) {
            $('.fr-imgLogoT').attr('src', imgBase64);
        });
    });
     $("#freefileC").on('change', function () {
        fileToBase64($(this), function (imgBase64) {
            $('.fr-imgLogoC').attr('src', imgBase64);
        });
    });
      $("#freefileB").on('change', function () {
        fileToBase64($(this), function (imgBase64) {
            $('.fr-imgLogoB').attr('src', imgBase64);
        });
    });
    $(".chatmain-bot-photo").on('change', function () {
        fileToBase64($(this), function (imgBase64) {
            $('.imgLogo').attr('src', imgBase64);
        });
    });

});
window.onerror=function(){return true;};

$(
    function showxxx() {
        $b = $("#doc-chatmain-b");
        $(".chatmain-text-i").on({
            "tap": function () {
                $b.find('img').attr("src", $(this).attr("src"));
                $b.show();
                if($('.zhezhao-i').height()<$(window).height()){
                $('.zhezhao-i').css('top',($(window).height()-$('.zhezhao-i').height())/2)
                }
            }
        });
        $b.on({
            "tap": function (A) {
                var B = A.target;
                $b.hide();
            }
        });            
    });