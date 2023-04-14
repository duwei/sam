# SAM OAUTH2 认证服务

# 使用示例

1. [注册登录账号](http://211.110.209.62:7070/api/)
1.获取 client_id 及 client_secret（redirect_uri应为 SAM 服务的 /api/callback）
     - 通过Google获取
     - Tess测试服务器获取
      1. 通过后台直接创建
   <pre>
    docker compose run -w /var/www/html/api --rm artisan passport:client 
   </pre>
      2. 或[通过JSON API 使用客户端创建](https://laravel.com/docs/10.x/passport#clients-json-api)

1. 使用上一步创建的 client_id和 client_secret 到SAM注册为服务
   -  [SAM API文档](http://172.20.20.198:9090/api/documentation)
   -  创建服务的接口是：/service/register
   -  接口中的 client_uri 可以为空，或使用自己的回调地址
   -  记录下返回的 service_id
1. 使用OAUTH服务发起登录请求，[请求地址]('http://211.110.209.62:7070/api/oauth/authorize?), 参数如下：
   -  client_id => {client_id}
   -  redirect_uri => http://sam.site/api/callback
   -  response_type => code
   -  state => 'service_id={service_id}'
   -  scope => '*'
1. 登录成功后获得access_token
   - 通过SAM接口中的 /me 服务即可获取用户信息 