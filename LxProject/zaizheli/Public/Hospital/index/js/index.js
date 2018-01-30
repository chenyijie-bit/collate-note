$(function () {
    //时间列表,选中填值
    var timeul = $("#timeul");
    var listObj = timeul.children("li");
    for (var i = 0; i < listObj.length; i++) {
        var curruntInput = $($(listObj[i]).children("input")[0]);
        curruntInput.click(function () {
            $(this).parent().siblings("li").children("input").removeClass("active");
            $(this).addClass("active");
            var dateStr = "";
            dateStr = $(this).parent().children("span")[0].innerHTML;
            $("#time-btn1").click(function () {

                if ($("#interdict1").hasClass("show")) {
                    $("#interdict1").removeClass("show").addClass("hide")
                }
//
                $("#doc-time").html(dateStr);
                $("#currunt-data").attr("value", dateStr);

            })
        })

    }
//开关按钮
    var div2 = $("#div2");
    var div1 = $("#div1");
    div2.click(function () {
        if ($("#currunt-color").attr("value") == "1") {
            $("#currunt-color").attr("value", "0")
        } else {
            $("#currunt-color").attr("value", "1")
        }

        if (div1.hasClass("close1")) {
            div1.removeClass("close1").addClass("open1")
        } else {
            div1.addClass("close1");
        }

        if (div2.hasClass("close2")) {
            div2.removeClass("close2").addClass("open2")
        } else {
            div2.addClass("close2")
        }
    });
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
    $("#chuzhen").click(function () {
        $("#currunt-status").attr("value", "1");
        if ($("#fuzhen").hasClass("select-currunt")) {
            $("#fuzhen").removeClass("select-currunt");
            $("#chuzhen").addClass("select-currunt");
        }
        else if ($("#chuzhen").hasClass("select-currunt")) {
            $("#chuzhen").removeClass("select-currunt")
        } else {
            $("#chuzhen").addClass("select-currunt");
        }
    });
    $("#fuzhen").click(function () {
        $("#currunt-status").attr("value", "2");
        if ($("#chuzhen").hasClass("select-currunt")) {
            $("#chuzhen").removeClass("select-currunt");
            $("#fuzhen").addClass("select-currunt");
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
    $(".close").click(function () {
        if ($("#interdict2").hasClass("show")) {
            $("#interdict2").removeClass("show").addClass("hide")
        }

        if ($("#interdict3").hasClass("show")) {
            $("#interdict3").removeClass("show").addClass("hide")
        }

    })
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
    });

//菜单栏样式切换
    $(function () {
        $(".tab-group").click(function () {
            $(this.children[0].children[0]).css("color", "#007aff");
            $($(".tab-personal").children().children()[0]).css("color", "");
            $($(".tab-home").children().children()[0]).css("color", "");
            $(".tab-group").addClass("group-currunt");
            $(".tab-home").removeClass("home-currunt");
            $(".tab-personal").removeClass("personal-currunt");
            window.location.href = "/Hospital/Advice/lists";
        });
        $(".tab-home").click(function () {
            window.location.href = "/Hospital/Index/index";
            $(this.children[0].children[0]).css("color", "#007aff");
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

            $(".tab-personal").addClass("personal-currunt");
            $(".tab-home").removeClass("home-currunt");
            $(".tab-group").removeClass("group-currunt");
            window.location.href = "/Hospital/My/index";

        })
    })
    //添加就诊人判断
    $(".addsuf-bot-but").on("tap", function () {
        if ($("#as-name-in").val() == '') {
            alert("请输入真实姓名");
            return false;
        } else if ($("#as-id-in").val() == "") {
            alert('请输入身份证号');
            return false;
        } else if ($("#as-age-in").val() == "") {
            alert("请输入真实年龄");
            return false;
        } else if ($("#as-tel-in").val() == "") {
            alert("请输入手机号码");
            return false;
        } else if ($("#as-name-in").val() != "" && $("#as-age-in").val() != "" && $("#as-tel-in").val() != "") {
            // 姓名
            var name = /^[\u4e00-\u9fa5]{2,4}$/;
            var nameV = $("#as-name-in").val();
            if (name.test(nameV) == false) {
                alert("姓名请输入长度2-4位汉字");
                return false;
            }
            ;
            // 身份证号
            var idV = $("#as-id-in").val();
            if (idV.length != 18) {
                alert("请输入正确的身份证号");
                return false;
            }
            // 年龄
            var ageV = $("#as-age-in").val();
            if (Number(ageV) < 0 || Number(ageV) > 120) {
                alert("请输入真实年龄");
                return false;
            }
            // 手机号码
            var reg1 = /^1(5|3|8)\d{9}$/;
            var str1 = $("#as-tel-in").val();
            if (reg1.test(str1) == false) {
                alert("请正确输入手机号码");
                return false;
            }
        }
        ;
        $("#addsuf-form").submit();

    });


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

$(function showxxx() {
    $b = $("#doc-chatmain-b");
    $("body").on( "tap",".chatmain-text-i",function () {
        $b.find('img').attr("src", $(this).attr("src"));
        $b.show();
        if($('.zhezhao-i').height()<$(window).height()){
            $('.zhezhao-i').css('top',($(window).height()-$('.zhezhao-i').height())/2)
        }
    });

    $b.on( "tap", function (A) {
        var B = A.target;
        $b.hide();
    });
});



