<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016101600697025",

		//商户私钥
		'merchant_private_key' => "MIIEogIBAAKCAQEAyxxdJIMY8l0D8khm+5OYZ6OhOPEVH0eD9z25YKXn/JeC8WgkU83SB1LV9WVl8PV1kcDy930W/Saj6KuOGWNj4ZaoP7NNa74ffnb9MsZ/5ltpVMzNfCkq9mS9UkpI7bdwOJQ9QyfNnsqpQbqVV2OAJa+72fxVQevmQ73ITwCvpy3Q8qSwEgh3ILpGR3dTkXNNseIvvWdBj8CCmETAXkj2kPkxAihPPwqebBlqvlSdpk40Eu0M26hVszCBzVLaMUUlRCARWHTT90Vg6IIuemZQ9uRmHMjUAcaGUBYfntovqGw9XcagDFqgFH8ocIotGEG4QP4RzI4dl/SVNCwv0VVniQIDAQABAoIBAEgMt73MA312IYBAzsdPhDC/iuF4I9YEuHtsGeZ+89xWosyf9MMFsAuJBv0WCyN+70XU/FtutEj4/Av9T1sSNsw/dU+je0NNHj7uVsM8RwKgSq0aOYph0Cu3cLFQSK6K8/QeId1AsWAy4/e9CF0Hnt2/iRO6v0OzOuus1vGvfKAArtwKWdkDEqy604PEd+LiYIre2UwomlzB840VaMvfm4l7+cnKiRCzSC333mrRzFbWGC1yt/vxD18tfxdztFZohXsw8C0OTtcAQRhP21IJV3nVowmybEdVguE0mKYSdMQluVYOE1AVjQIRX/7kiVSVCZaaZW3+EMHhYptHPMpYWAECgYEA6VNbHJ42cbY4M/eEk3V30sw/M2/j6sFnQMLcUKbOQdwiWD8cbvjdj4batHzKjtiPIGnsVAkOozr4AOdS2F3BGQ5qDvFKkJeB+JLDjw+GWeZ0sR9pDRiiFCIyaPjxll+JdTeMs0eQtEQYz0B0CAVpYUWSSZHmGSEI2t7KEeLOQnkCgYEA3tlT5/w5I167J0tHi1kutnqFhDK2L3UP9bs7y9k29+jobe3P8ONgDgAzectQt9kr+Kodu6gKJ/vd9lxd9hYmL2gHopRJJCFnLCkopb1HdVr+zjEc762/YNbFTP6Vq+NJWbn3ORP5syyQQhq3y0s+MjT9iFtjfpAXjI/E3nyniZECgYApZBojp4VtArBRt7UTG6GYCZeknlLGUGzqtiazQAQS9uQ412lJ3mgfkAK4RfMBCDXLwD6mYbzxBAL+1gqCS493zHDQGcnqgJYT7KU4eb6RsCo/SZ0vL5GBFNfwe0kQLf6KhsI1A5sN/46PJZXv3lXlZZwf5Pv2tT+G5ELgjWWdsQKBgH6oBohTUiZIMRakMoWAqowMFtaL+//9CJUhVVsGmbdNKM1R3F2M3YsXkiom5DmZtOQfy8ZcO2l+PSlIVh8Hd6x589zzap6eXYU7315Ttcl9blTClS+kEKF5b6nTCD4pcAcPKvPF5qVvLo5joRJqMW9XrsFRL7s/KiM5kyrgrlgBAoGAU5LA0skF+jKxl5MT0GTMNnUdBw8nHoXyw5ekIRD86/BoTZCEXUmaT+VDF+Gb0HRzJMbGE75ErC4+omJuRzIYx6iKXYY6bB2IBEJ2D10BNRAD2uKuCGklmYq0adF55zWfgyRNFJe+/xX6EwR3UE3wx6t+lUxAN//EBNanJVkBVEA=",
		
		//异步通知地址
		'notify_url' => "http://www.pyg.com/home/order/notify",
		
		//同步跳转
		'return_url' => "http://www.pyg.com/home/order/callback",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAg3rCpG6EU2N8yJ0WoVfREWj/Wuk9NRlJDpfj6bL0GKB2qLrG7Tf2+yiaUqRmFoanEQnGpDSYfDgod7gm2nuvoDnANspHAvbMnQ7qI++zBfH2nB5YM8H30PDccYvOClRQu7m7nhD+CMPxdtLARWhkLtWO3qtmTnY6Tdtz+aY8Rsr+axEtdFx+waejzY/L9Uwb/rN3v4qojCySFazPh7IJAfcM2iiScrZhfjj0ZWlmX3j+5NKzwQ7OLeC3QDuqRYFlHNKC1uTnQFjQyUhVaYpJWUFzSc981W3VPyGl6Lx8wYKD7WlX0m/oFcHJ/U8uvqtH6ziGBsk+tDuuzdW1mPQFuwIDAQAB",
);