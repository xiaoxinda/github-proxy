var webSocketClick = {
    ws:null,
    app_block:null,
    githubUrl:null,
    init:function(){
        var __this = this;
        __this.app_block = $(".app-block");
        __this.ws = new WebSocket("wss://" + document.domain + ":8848");
        __this.ws.onopen = function(event){
            __this.onOpen(event)
        }
        __this.ws.onmessage = function(event){
            __this.onMessage(event)
        }
        __this.ws.onclose = function(event){
            __this.onClose(event)
        }
        __this.app_block.find(".sub-btn").on('click',function(){
            __this.getSource($(this))
        })
        $(".exemption").on("click",function(){
            var html = '<div style="font-size:1rem;margin:15px;">\
                <span style="font-size:1.5rem;">您浏览并使用本站的代下载服务，视为对以下声明内容的全部认可</span>\
                <p>1、本站不存储资源，只是中转平台</p>\
                <p>2、使用本站下载任何资源需自行承担风险</p>\
                <p>3、本站仅代下网上现成的代码，无法对这些代码的可用性，准确性或可靠性作出任何承诺与保证</p>\
                <p>4、本站不对任何由于使用或无法使用本站提供的代码所造成的直接的和间接的损失负任何责任</p>\
                <p>5、任何单位和个人不得使用本站下载侵权、盗版、违法的任何软件或资料</p>\
                </div>';
            layer.alert(html,{title:'免责声明',area:'500px'})
            return false;
        })
    },
    onOpen:function(event){
        var __this = this;
        sendJson = {
            'type':"2",
        };
        __this.ws.send(JSON.stringify(sendJson)+"\n")
        console.log("连接成功")
    },
    onMessage:function(event){
        var __this = this;
        try {
            var data = JSON.parse(event.data)
            if (data.status == 1) {
                layer.msg(data.msg);
                return false;
            }
            switch (data.data.sta) {
                case 1: //打包
                case 2: //压缩zip
                    __this.app_block.find(".sub-btn").text(data.msg)
                    break;

                case 3:
                    layer.msg("操作完成，请下载");
                    __this.app_block.find(".sub-btn").html("<a class='download-btn' href=\"" + data.data.url + "\" target='_blank'>" + data.msg + "</a>").css({
                        "background": "green",
                        "color": "#fff",
                    });
                    $(".download-btn").on("click",function(){
                        sendJson = {
                            'type':"1",
                        };
                        __this.ws.send(JSON.stringify(sendJson)+"\n")
                        sendJson.type = "2";
                        __this.ws.send(JSON.stringify(sendJson)+"\n")
                        setTimeout(function(){
                            window.location.reload();
                        },2000);
                    })

                    break;

                case 4:
                    $(".down_num").find("b").text($.trim(data.data.down_num))
                    break;
            }
        }catch (e) {

        }




    },
    onClose:function (event) {
        var __this = this;
        console.log("连接关闭")
    },
    getSource:function($this){
        var __this = this;
        var githubUrl = __this.app_block.find(".text");
        var githubUrlReg = new RegExp('^https:\\/\\/github.com\\/([\\w\\/\\d\\-\\_\\.]+)$','ig');
        var githubUrlReg1 = new RegExp('^((https|http):\\/\\/)?([\\w\\.\\/\\-\\_^]+?)\\.([tar\\.gz|gz|tar\\.bz2|bz2|tar|zip|tar\\.xz|tar\\.z|rpm|deb|rar]+?)$','ig');
        if(!githubUrl.val()){
            layer.msg("请输入github地址");
            githubUrl.select();
            return false;
        }
        var urlVal = $.trim(githubUrl.val())
        if(!githubUrlReg.test(urlVal) && !githubUrlReg1.test(urlVal)){
            layer.msg("请输入正确的github地址：仅支持https协议 或<br> （gz|tar.gz|bz2|tar|tar.bz2|zip|tar.xz|tar.z|rpm|deb|rar）格式的压缩包下载链接");
            githubUrl.select();
            return false;
        }
        __this.githubUrl = urlVal
        __this.getSourceInit();

    },
    getSourceInit:function(){
        var __this = this;
        var sendJson = {
            "url" : __this.githubUrl,
        }
        __this.app_block.find(".sub-btn").prop("disabled",true).off("click").css({
            "background": 'none',
            "color":"#EACB20",
        });
        __this.ws.send(JSON.stringify(sendJson)+"\n")
    }
}
$(function(){
    webSocketClick.init();
})