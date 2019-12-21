<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2021000197614135",
		//编码格式
		'charset' => "UTF-8",
		//签名方式
		'sign_type'=>"RSA2",
		//scope auth_user获取用户信息; auth_base静默授权
		'scope' => 'auth_user',
		//跳转地址
		'redirect_url' => 'http://'.$_SERVER['HTTP_HOST'].'/home/login/alicallback',
        //支付宝授权地址 沙箱环境
//        'oauth_url' => 'https://openauth.alipaydev.com/oauth2/publicAppAuthorize.htm',
        //支付宝授权地址 正式环境
         'oauth_url' => 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm',
        //支付宝网关 沙箱环境
//        'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",
        //支付宝网关 正式环境
        'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//商户私钥
		'merchant_private_key' => "MIIEogIBAAKCAQEAoDxKOnq6AXtzXxskw4is1cfyhNWeUeqOi3BbJVcFpq8lMdX2gLT9PPT5zp2ydDspVmxhvlXsNsewmtRZpYaUM0GwG4mgh5pEeuz8we5TT5/REe51mIYKMxcU+QnMRGWEVYmx/HI4NNTUQlLJhAf6TnBcYZSnyFJqUitut8aCnvqk7oq9Wx9E2D3HCzibV3sTsBcW/h8G6AsykstfGs9QbrxDsAMlw7oD7t8N2i1D5a+nIQoKSFI6bgxNLofG10yhBBH4NuJkfU+7UIKeqI4KQz1ovyKkey6MWuVzJ0uy3AOhrJ1Z5g1VbdSoelglxfK/i4B7ZxzIrPkRw35MKh8r8QIDAQABAoIBACSNXEl20ZOg+7KIWJyaQwMJd5NBv7nkDdxVJxWfglSBw7RYoMC/iL0tDQdLBfhpvoZOdwWaSCY+61SGeGTADHKc23WMsaf9Uv3PreD3Yt3ZETJL+VtvyEfbThd5WrWHiuaQYJ+71oKGwlzW0c1inIDkNM61wIh79HEGI12c1RM6sZOQOthVe+cfqLqKO6wY4t6TE8Gq0ptTTmSbc0UVB4UuGGzdJA/9MykMQ5j/6DCXOIjC3EgclzoNwzKCcOD8+w4rIwiqk3YLW0wQOhEjPK6UFQEGuLPSzaBFmW+ZpZ0LomBHzJTXpvAJLnyswZlDmz5IZ2fg2WUAxKXfDYzOvNkCgYEAzy6ALTfazOj0rB+ViyKmtmM+8vUkUWqr7kGnHbmunZ6e25H4GWWJx3QRkoQ2loLbRLs7yWEmyleG0KmfpsKzHO+vDEL8vr4F9Rzax2GfdUZwNd7qEw92Qypgb2p+35b5Pn+58kYXZ+w+NsFppv64gz0vazjNVZWgOk3Ue7Oq3n8CgYEAxf3uByNdU/+p0pYawTEWRjoCU/gEU+qFN8irBXivVDfBHdFwXutVwHcdIWFXnI/gCyKoHkMb+CRosnRNiuh9xlQvqrbxde1WSSyTFa/YDNtc6IoyKx07qy/8IeOVfqqhqyF7Gz3wudUDNI8PVXm0bAO7TePaGIsZzEizlbJHnY8CgYAk+XeGmmFCEJEZ5skZe6WlBzFEytsgbJkifDUG6QxwtM5FAVf1sFlWbzBzpYTYd6IKMiAJYH07v0MgGiWT7Utp7pye45WXtPH7PJsO2i1LELcV5iKAJFJGyllLiQX7gUYaEVFuG3Qx58dynjHsy6jCQzkjOY/rpjhiL2GscfQcoQKBgBV+svfNbBVxfTNinnb46E0pgRUyqO2fWWUsAP0wFADVqL6e/U8gx4eEpwH3unZB3HX5Bj161a+D7GXrYHvDL9x3SVGEWQSPhr2c/D7vf0ouCpEoZ7fsjYzRQdNADPvadIDKur3DZL4YFSgAnA6QjvjnEs+q/AOMHnniLrUCzOmbAoGAdRrP38c2FmFT2Xw4W9mFFvIBW0XFUQkm0sLEzPb/qia6AjeE9p/XWNiWlJgaJXbQ5/VO70WZXrXmtdG/fXW+tcn6mi0cIR793o1cBq5j5T7tlURupoTGb4VMlhTJstZHDyOqPpQNnEvEdQfiKsidXvCL+Eu37YAjwNB8LIF0k14=",

);