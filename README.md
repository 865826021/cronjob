cronjob
=======
提供任务队列和计划任务功能

环境要求：
=======
cron服务，支持php的web环境，mysql服务

安装说明：
=======
1.安装配置curl和php5-curl。
2.复制cronjob文件夹到固定路径。
3.配置web服务器和mysql服务器，导入程序文件，数据文件。
4.修改crontab.sh里url地址和执行时间间隔。
5.复制crontab.sh到固定路径并给予可执行权限。
6.在文件/etc/crontab添加任务。
　 *  *    * * *   root    路径/crontab.sh
7.完成。

安装说明2：
=======
1.安装配置curl和php5-curl。
2.复制cronjob文件夹到固定路径。
3.配置web服务器和mysql服务器，导入程序文件，数据文件。
4.执行index.php?act=run&auth=md5(date(YmdH).密码)
5.完成

更新说明
=======
*20131127
    增加php方式。部署环境要求降低。