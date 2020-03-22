**（注:本程序为简单实现，因纯属业余时间编写，开发比较仓促，欢迎完善）**<br>
1.staticweb目录您需要进行nginx配置为站点。<br>
2.GatewayWorker下start_gateway中配置wss所需要的证书<br>
`$context = array(`<br>
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`'ssl' => array(// 请使用绝对路径`<br>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`'local_cert'                 =>'/xxxxx.pem', //也可以是crt文件`<br>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`'local_pk'                   => 'xxxxxx.key',`<br>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`'verify_peer'               => false,
         // 'allow_self_signed' => true, //如果是自签名证书需要开启此选项`<br>
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`)`<br>
 `);`<br>
3.启动GatewayWorker<br>
 `cd 您的GatewayWorker路径/GatewayWorker ; php start.php start`
4.start_sync中可根据您的服务器配置来设置：进程数<br>
task进程数可以根据需要多开一些，默认为10<br>
`$task_worker->count = 10;`<br>
5.demo站点：<a href="https://g.widora.cn">点我跳转</a><br>
6.本程序基于<a href="https://www.workerman.net/">workerMan</a>编写<br>
7.因本人平常工作繁忙，欢迎爱好者帮忙搭建docker，请联系本人放出仓库地址<br>
8.如有问题，可联系本人qq：9496898<br>
9.另外，部署到香港、韩国、日本等非大陆的服务器的事情就不用我说了吧。推荐使用：<a href="https://www.vultr.com/?ref=8428612">www.vultr.com</a>日本服务器<br>
10.如果不愿意搭建本服务，平常工作又有用到梯子的情况，推荐使用trojan节点：<a href="https://portal.shadowsocks.nl/aff.php?aff=32558">order.shadowsocks.nl</a>

