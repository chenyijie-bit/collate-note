<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2017111309909702",

		//商户私钥
		'merchant_private_key' => "MIIEogIBAAKCAQEAsVcYRprdlEJ4fVs7BKk2At4Ck7jLg88FI2Hu4NsiEJL5JmzPRpFPctLOBme0crZoeCgBuZuuRQnHQqrpL7sdLzosLg5ShclhaBEI0WaeBduj0rt4yRBQ7sIN07OBYUX8U75BGXLCpHagCHkoKM9ZeoT/4Zk1s5887XgXrzrKBIvXARmBs1We6UWHQnVh7/xwWo6C6L/LjSwFK2AiWojmXGxKbmtf6EIch5H7oJzqWEGi8ADH9hkcojebNBxI3tI+vXqceD3TgvhScZggwv2VEQL4TfQ7e6GoR8wWxWR4OEW2kUUJ1Iuf5pY3lnJV8qugsmyqyNvX9wUtqCvKdICZ5QIDAQABAoIBABlaIx1M3GpqikEZfrlu20rTpDisDWQdf1WMlZLNoPQPntCwc31aHqqCmnNt9e0ESLEMvxpiuCokeLj+J/Hr5QMwZMp8v61imas/7CvLaMHboXLp3B2aWIeZdFKUceWPFMCADVxu/IZ4cu5jK6uR4O/T/aSpu3SfSh2EspYZaHH3srBRxZF4QVRLz4WjVm64I+3xxfx714zjkQDjNlvLkcvOHTUc/ZUc4blJCHzE0KcB75vQA7dMFIA0cXxPBWqyUomNKoyCdggmPW9JHa0zIn6ZCnzmYowQwupZM8B/wXZnW44pdjqLyiHL0DX2dziMTlNeUyXX3sSQPssSDTREcM0CgYEA6JNIX53qE2vxbOGdn62GDoaXjlavhjBTCmOxWZIYO6WIAPGvMqz74Elo9p1rLafTnTaChAk0ieircsTVYcsb4jMQIZE7yuDxu44PQ/+FZH2qeJPUPMeivfd6FlIhz8yG4Gd77xJ7Qml2eHNNPFtOrCP77IB49z2mQxFtTPJJKqcCgYEAwzOhl5knmF8arDHuWp4XjLKALdy+wxamDOIuaKEKZ1mjCWDp5feiZ3cORoOODcrIEOE0nL9nWiDxkPvRjYsgQ3XnUke8NKmrSJA8TRWjbMcqPJ7b5BO7LeIlZ+9aUlhupCxThJyTHryLfE77UPoZI+QHef6/RXvWHzTfi4RphJMCgYB3qSeogoAnw/bwVVibClWZ7afWhUVD3mMrSkW6Vw9+yNkj2zWP9i6VpE+L60x0rg+TqLMYKgBNIFft8dXzveO3yxv2mVnRNVFKdXnnO3WvUXS/GxgsuW5DHSxEhbd9ybZviO7b/39JmSdqK7DGaBgfO1hnw2X5l0+O3E2HNHVuqwKBgDX7dVjDVhvhUTMFq/ELf1+9jY0hWvAAt3MgqcztnD7wnxHc51JdpWAPoLcHcqWFysZAQZiHpkFakvORcGZAb2+4j3xFslquAVxT5xk6PrO6cIfLNuxgOId73vRbURMsuYxVZdNqqZT6d2itPvsp7wHp8ddfB+5jTNfce2XN/JBZAoGADugJa+/qDjWKrlNuUoJUEsppPLk36DvmR0Whk8OJ6Lqw3MbURbH//P9yCj3dTkOFqQ5Gdt+PHHY7IEQAP3JaNju/iJGF+vmQRdr/Mj9yz9CKxv23PT9iD2FxqktO4lP7YVtO8GBskpDkFDpIitt1Pep84IIwR6XAd3k/n8oyxpQ=",
		
		//异步通知地址
		'notify_url' => "http://gx.zzlhi.com/business/alipay/notify_url",
		
		//同步跳转
		'return_url' => "http://gx.zzlhi.com/business/alipay/return_url",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsVcYRprdlEJ4fVs7BKk2At4Ck7jLg88FI2Hu4NsiEJL5JmzPRpFPctLOBme0crZoeCgBuZuuRQnHQqrpL7sdLzosLg5ShclhaBEI0WaeBduj0rt4yRBQ7sIN07OBYUX8U75BGXLCpHagCHkoKM9ZeoT/4Zk1s5887XgXrzrKBIvXARmBs1We6UWHQnVh7/xwWo6C6L/LjSwFK2AiWojmXGxKbmtf6EIch5H7oJzqWEGi8ADH9hkcojebNBxI3tI+vXqceD3TgvhScZggwv2VEQL4TfQ7e6GoR8wWxWR4OEW2kUUJ1Iuf5pY3lnJV8qugsmyqyNvX9wUtqCvKdICZ5QIDAQAB",
);