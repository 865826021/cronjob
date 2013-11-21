#!/bin/bash
#使用方法，在/etc/crontab中添加
#*  *    * * *   root    路径/crontab.sh
#间隔时间，范围1-60秒
step=5;
url='http://localhost/cronjob/';
for i in $(seq `echo "scale=0;60/$step"|bc`) ;
do curl $url;sleep $step;
done;
