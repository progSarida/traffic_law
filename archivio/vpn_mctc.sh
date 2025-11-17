#!/bin/bash
#--servercert 
CRED=/home/ovunque/sistema/script/utemysql.conf;

sql="select password from CustomerService where serviceid=6 and cityid='XXXX';";
pchiave=`echo "$sql" | mysql --defaults-extra-file=$CRED -BN traffic_law`;
echo "$pchiave" | sudo openconnect -b --no-dtls --authgroup=default --user=PRI.185450860 --protocol=anyconnect --useragent=AnyConnect --passwd-on-stdin anyvpn.ilportaledellautomobilista.it/utentiMCTC;
