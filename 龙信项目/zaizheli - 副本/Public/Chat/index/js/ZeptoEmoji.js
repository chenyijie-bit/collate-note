var AllPass;
(function (AllPass) {
    var Emoji = (function () {
        function Emoji() {
            this.data = {
                113: "[/\u5472\u7259]",
                112: "[/\u8c03\u76ae]",
                127: "[/\u6d41\u6c57]",
                120: "[/\u5077\u7b11]",
                139: "[/\u518d\u89c1]",
                138: "[/\u6572\u6253]",
                140: "[/\u64e6\u6c57]",
                162: "[/\u732a\u5934]",
                163: "[/\u73ab\u7470]",
                105: "[/\u6d41\u6cea]",
                109: "[/\u5927\u54ed]",
                133: "[/\u5618]",
                116: "[/\u9177]",
                118: "[/\u6293\u72c2]",
                149: "[/\u59d4\u5c48]",
                174: "[/\u4fbf\u4fbf]",
                170: "[/\u70b8\u5f39]",
                155: "[/\u83dc\u5200]",
                121: "[/\u53ef\u7231]",
                102: "[/\u8272]",
                106: "[/\u5bb3\u7f9e]",
                104: "[/\u5f97\u610f]",
                119: "[/\u5410]",
                100: "[/\u5fae\u7b11]",
                111: "[/\u53d1\u6012]",
                110: "[/\u5c34\u5c2c]",
                126: "[/\u60ca\u6050]",
                117: "[/\u51b7\u6c57]",
                166: "[/\u7231\u5fc3]",
                165: "[/\u793a\u7231]",
                122: "[/\u767d\u773c]",
                123: "[/\u50b2\u6162]",
                115: "[/\u96be\u8fc7]",
                114: "[/\u60ca\u8bb6]",
                132: "[/\u7591\u95ee]",
                108: "[/\u7761]",
                152: "[/\u4eb2\u4eb2]",
                128: "[/\u61a8\u7b11]",
                190: "[/\u7231\u60c5]",
                136: "[/\u8870]",
                101: "[/\u6487\u5634]",
                151: "[/\u9634\u9669]",
                130: "[/\u594b\u6597]",
                103: "[/\u53d1\u5446]",
                146: "[/\u53f3\u54fc\u54fc]",
                178: "[/\u62e5\u62b1]",
                144: "[/\u574f\u7b11]",
                191: "[/\u98de\u543b]",
                148: "[/\u9119\u89c6]",
                134: "[/\u6655]",
                129: "[/\u5927\u5175]",
                154: "[/\u53ef\u601c]",
                179: "[/\u5f3a]",
                180: "[/\u5f31]",
                181: "[/\u63e1\u624b]",
                182: "[/\u80dc\u5229]",
                183: "[/\u62b1\u62f3]",
                164: "[/\u51cb\u8c22]",
                161: "[/\u996d]",
                168: "[/\u86cb\u7cd5]",
                156: "[/\u897f\u74dc]",
                157: "[/\u5564\u9152]",
                173: "[/\u74e2\u866b]",
                184: "[/\u52fe\u5f15]",
                189: "[/OK]",
                187: "[/\u7231\u4f60]",
                160: "[/\u5496\u5561]",
                175: "[/\u6708\u4eae]",
                171: "[/\u5200]",
                193: "[/\u53d1\u6296]",
                186: "[/\u5dee\u52b2]",
                185: "[/\u62f3\u5934]",
                167: "[/\u5fc3\u788e]",
                176: "[/\u592a\u9633]",
                177: "[/\u793c\u7269]",
                172: "[/\u8db3\u7403]",
                137: "[/\u9ab7\u9ac5]",
                199: "[/\u6325\u624b]",
                169: "[/\u95ea\u7535]",
                124: "[/\u9965\u997f]",
                125: "[/\u56f0]",
                131: "[/\u5492\u9a82]",
                135: "[/\u6298\u78e8]",
                141: "[/\u62a0\u9f3b]",
                142: "[/\u9f13\u638c]",
                143: "[/\u7cd7\u5927\u4e86]",
                145: "[/\u5de6\u54fc\u54fc]",
                147: "[/\u54c8\u6b20]",
                150: "[/\u5feb\u54ed\u4e86]",
                153: "[/\u5413]",
                158: "[/\u7bee\u7403]",
                159: "[/\u4e52\u4e53]",
                188: "[/NO]",
                192: "[/\u8df3\u8df3]",
                194: "[/\u6004\u706b]",
                195: "[/\u8f6c\u5708]",
                196: "[/\u78d5\u5934]",
                197: "[/\u56de\u5934]",
                198: "[/\u8df3\u7ef3]",
                200: "[/\u6fc0\u52a8]",
                201: "[/\u8857\u821e]",
                202: "[/\u732e\u543b]",
                203: "[/\u5de6\u592a\u6781]",
                204: "[/\u53f3\u592a\u6781]",
                107: "[/\u95ed\u5634]"
            };
            this.lastTapDate = new Date;
            this.el = $(document.getElementById("emojiContainer"));
            this.scroller = null;
            this.default_width = $(window).width();
            /*计算移动变量*/
            this.startX = 0;
            this.x = 0;
            this.curX = 0;
            this.clientX = 0;
            this.clientY = 0;
            this.isMove = false;
            this.width = 0;
            this.index = 0;
            /*计算移动变量*/
            this.emoji = [];
            this.input = $(document.getElementById("emojiInput"));
        }
        Emoji.prototype.Init = function () {
            this.scroller = this.el.find(".select-area ul")[0];
            this.index = 0;
            var img;
            if (window.devicePixelRatio >= 1.5 || $.browser.ie)
                {img = "/Public/Chat/index/images/emoji/1@2x.png?max_age=19830211";}
            else
                {img = "/Public/Chat/index/images/emoji/1.png?max_age=19830211";}
            var imgPath = img;
            (new Image).src = imgPath;
            this.width = this.default_width;
            this.Render(imgPath);
            this.Bind();
        };

        Emoji.prototype.Render = function (imgPath) {
            var html = this.widgetEmojiSelector({
                imgPath: imgPath
            });
            this.el.html(html);
            this.scroller = this.el.find(".select-area ul")[0];
        };
        
        Emoji.prototype.Bind = function () {
            var self = this;
            this.Unbind();
            this.el.find("[data-emoji]").on("tap", function () {
                var height_text = $("#emojiInput").height();
                var now = new Date;

                if (now - self.lastTapDate < 500)
                    return;
                self.lastTapDate = now;
                var id = this.getAttribute("data-emoji");
                self.addEmoj({
                    ubb: "[em]e" + id + "[/em]",
                    str: self.data[id],
                });
                var cbt = $("#emojiInput");
                // setInterval('text()', 100);
            });
            this.el.find('[data-oper="delete"]').on("tap", function () {
                var now = new Date;
                if (now - self.lastTapDate < 500)
                    return;
                self.lastTapDate = now;
                self.removeEmoj();
            });
            this.el.on("touchstart touchmove touchend touchcancel", function (e) {
                self.HandleEvent(e);
            });
        };
        Emoji.prototype.HandleEvent = function (e) {
            switch (e.type) {
                case "touchstart":
                    this.onTouchStart(e);
                    break;
                case "touchmove":
                    this.onTouchMove(e);
                    break;
                case "touchcancel":
                case "touchend":
                    this.onTouchEnd(e);
                    break;
            }
        };
        Emoji.prototype.onTouchStart = function (e) {
            var point = e.touches ? e.touches[0] : e;
            this.startX = this.x = this.scroller.getBoundingClientRect().left - this.scroller.parentNode.getBoundingClientRect().left;
            this.curX = point.clientX - this.x;
            this.clientX = point.clientX;
            this.clientY = point.clientY;
            this.isMove = false;
        };
        Emoji.prototype.onTouchMove = function (e) {
            var point = e.touches ? e.touches[0] : e;
            this.x = point.clientX - this.curX;
            this.x = Math.min(this.x > 0, Math.max(this.width * -4, this.x));
            $(this.scroller).animate({
                translateX: this.x + "px"
            }, 0);
            this.isMove = true;
            e.preventDefault();
        };
        Emoji.prototype.onTouchEnd = function (e) {
            if (this.isMove) {
                var self = this;
                var dist = this.x - this.startX;
                if (Math.abs(dist) > this.width / 10) {
                    this.index = Math.max(0, Math.min(4, dist < 0 ? this.index + 1 : this.index - 1));
                    $(this.scroller).animate({
                        translateX: -this.index * this.width + "px"
                    }, 300);
                    this.el.find(".page-area i").each(function (i, el) {
                        i == self.index ? $(el).addClass("current") : $(el).removeClass("current");
                    });
                } else
                    $(this.scroller).animate({
                        translateX: -this.index * this.width + "px"
                    }, 200);
                e.preventDefault();
            }
        };

        Emoji.prototype.Unbind = function () {
            this.el.find("[data-emoji]").off("tap");
            this.el.find('[data-oper="delete"]').off("tap");
            this.el.off("touchstart touchmove touchend touchcancel");
        };
        Emoji.prototype.widgetEmojiSelector = function (data) {
            var __p = [], _p = function (s) {
                __p.push(s);
            };
            if (data) {
                __p.push('\t<div class="select-area">');
                var imgPath = data && data.imgPath || "../images/emoji/1" + (!window.devicePixelRatio || window.devicePixelRatio >= 1.5 ? "@2x" : "") + ".png?max_age=19830211";
                __p.push('\t\t<ul style="-webkit-transform:translateZ(0px);">\n\t\t\t<li style="background-image:url(');
                _p(imgPath);
                __p.push(')">\n\t\t\t\t<i data-emoji="100"></i><i data-emoji="101"></i><i data-emoji="102"></i><i data-emoji="103"></i><i data-emoji="104"></i><i data-emoji="105"></i><i data-emoji="106"></i><i data-emoji="107"></i><i data-emoji="108"></i><i data-emoji="109"></i><i data-emoji="110"></i><i data-emoji="111"></i><i data-emoji="112"></i><i data-emoji="113"></i><i data-emoji="114"></i><i data-emoji="115"></i><i data-emoji="116"></i><i data-emoji="117"></i><i data-emoji="118"></i><i data-emoji="119"></i><i data-oper="delete"></i>\n\t\t\t</li>\n\t\t\t<li>\n\t\t\t\t<i data-emoji="120"></i><i data-emoji="121"></i><i data-emoji="122"></i><i data-emoji="123"></i><i data-emoji="124"></i><i data-emoji="125"></i><i data-emoji="126"></i><i data-emoji="127"></i><i data-emoji="128"></i><i data-emoji="129"></i><i data-emoji="130"></i><i data-emoji="131"></i><i data-emoji="132"></i><i data-emoji="133"></i><i data-emoji="134"></i><i data-emoji="135"></i><i data-emoji="136"></i><i data-emoji="137"></i><i data-emoji="138"></i><i data-emoji="139"></i><i data-oper="delete"></i>\n\t\t\t</li>\n\t\t\t<li>\n\t\t\t\t<i data-emoji="140"></i><i data-emoji="141"></i><i data-emoji="142"></i><i data-emoji="143"></i><i data-emoji="144"></i><i data-emoji="145"></i><i data-emoji="146"></i><i data-emoji="147"></i><i data-emoji="148"></i><i data-emoji="149"></i><i data-emoji="150"></i><i data-emoji="151"></i><i data-emoji="152"></i><i data-emoji="153"></i><i data-emoji="154"></i><i data-emoji="155"></i><i data-emoji="156"></i><i data-emoji="157"></i><i data-emoji="158"></i><i data-emoji="159"></i><i data-oper="delete"></i>\n\t\t\t</li>\n\t\t\t<li>\n\t\t\t\t<i data-emoji="160"></i><i data-emoji="161"></i><i data-emoji="162"></i><i data-emoji="163"></i><i data-emoji="164"></i><i data-emoji="165"></i><i data-emoji="166"></i><i data-emoji="167"></i><i data-emoji="168"></i><i data-emoji="169"></i><i data-emoji="170"></i><i data-emoji="171"></i><i data-emoji="172"></i><i data-emoji="173"></i><i data-emoji="174"></i><i data-emoji="175"></i><i data-emoji="176"></i><i data-emoji="177"></i><i data-emoji="178"></i><i data-emoji="179"></i><i data-oper="delete"></i>\n\t\t\t</li>\n\t\t\t<li>\n\t\t\t\t<i data-emoji="180"></i><i data-emoji="181"></i><i data-emoji="182"></i><i data-emoji="183"></i><i data-emoji="184"></i><i data-emoji="185"></i><i data-emoji="186"></i><i data-emoji="187"></i><i data-emoji="188"></i><i data-emoji="189"></i><i data-emoji="190"></i><i data-emoji="191"></i><i data-emoji="192"></i><i data-emoji="193"></i><i data-emoji="194"></i><i data-emoji="195"></i><i data-emoji="196"></i><i data-emoji="197"></i><i data-emoji="198"></i><i data-emoji="199"></i><i data-oper="delete"></i>\n\t\t\t</li>\n\t\t</ul>\n\t</div>\n\t<div class="page-area"><i class="current"></i><i></i><i></i><i></i><i></i></div>');
            }
            return __p.join("");
        };

        Emoji.prototype.getRelatedInput = function () {
            return this.input;
        };
        Emoji.prototype.addEmoj = function (obj) {
            var input = this.getRelatedInput();
            input.val(input.val() + obj.str);
            this.emoji.push(obj);
        };
        Emoji.prototype.removeEmoj = function () {
            var input = this.getRelatedInput();
            var value = input.val();
            if (value == "")
                return;
            var emoji = this.emoji[this.emoji.length - 1];
            if (emoji && value.lastIndexOf(emoji.str) + emoji.str.length == value.length) {
                value = value.substring(0, value.length - emoji.str.length);
                this.emoji.splice(this.emoji.length - 1, 1);
            } else
                value = value.substring(0, value.length - 1);
            input.val(value);
        };
        return Emoji;
    })();
    AllPass.Emoji = Emoji;
})(AllPass || (AllPass = {}));
window.onload = function () {
    var emoj = new AllPass.Emoji;
    emoj.Init();
};
  