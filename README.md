# nginx-load-balancer-test
for testing nginx load balancer function
# 测试Nginx负载均衡的两种方法
#### 前言
为了测试Nginx在4层负载均衡和7层负载均衡上请求的http referer参数而写
为了验证nginx的4层负载均衡能搭配k8s的ingress进行测试机的负载均衡而写
默认nginx都装上了stream模块

假设有三台负载机器，分别是NODE1,NODE2,NODE3
对通用的80和443端口进行负载
##### 4层代理配置例子
```
   stream{
       upstream http_server{
           
           server NODE1:80;
           server NODE2:80;
           server NODE3:80;
           least_conn;
       }
       upstream https_server{
           
           server NODE1:443;
           server NODE2:443;
           server NODE3:443;
           least_conn;
       }
       server {
           listen 80;
           proxy_pass http_server;
       }
       server {
           listen 443;
           proxy_pass https_server;
       }
   }
```
7层负载例子
```
map *$http_upgrade $connection_upgrade {*
default *upgrade;*
'' *close;*
}

upstream *http_server {*
ip_hash;
        server *NODE1:80;*
server *NODE2:80;*
server *NODE3:80;*
}
upstream *https_server {*
ip_hash;
    server *NODE1:443;*
server *NODE2:443;*
server *NODE3:443;*
}
server *{*
listen *80 default_server;*
server_name *a.example.com;*
location */ {*
proxy_set_header *Host $http_host;*
proxy_set_header *X-Real-IP $remote_addr;*
proxy_set_header *X-Forwarded-For $proxy_add_x_forwarded_for;*
proxy_set_header *X-NginX-Proxy true;*
proxy_set_header *Upgrade $http_upgrade;*
proxy_set_header *Connection $connection_upgrade;*
proxy_pass *http://http_server;*
}
}

server *{*

listen *443 ssl default_server;*
server_name *a.example.com;*
ssl_certificate        */etc/ssl/certs/a.example.com.pem;*
ssl_certificate_key    */etc/ssl/certs/a.example.com.key;*
ssl_protocols  *TLSv1 TLSv1.1 TLSv1.2;*
ssl_ciphers    *HIGH:!aNULL:!MD5;*
location */ {*
proxy_set_header *Host $http_host;*
proxy_set_header *X-Real-IP $remote_addr;*
proxy_set_header *X-Forwarded-For $proxy_add_x_forwarded_for;*
proxy_set_header *X-NginX-Proxy true;*
proxy_set_header *Upgrade $http_upgrade;*
proxy_set_header *Connection $connection_upgrade;*
proxy_pass *https://https_server;*
}
}

```
