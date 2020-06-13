<?php

return [
    'alipay' => [
        'app_id' => '2016102900775213',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAl6oCNcuwHGsvmK8ycnkOfpIUZkt7gD9Qa55lzHFzFz5X7DFeifFDla8WnmdHyzBxsoH8UPHrsbMz4jxI+Ztcpyn7ntDovBD28DTxTfNjSjeTM4PSvsfeLWOtdC1rPViQiDgFcLbA+FK1x13aPvNlWjTMSK0sfsckN3t5vOcnnV70f36SZ8EQf7gpT/fvZLSeRqWPnZRtgSxL+gNLN41KuOmLOOFWkb5h0Dn9ddvpZfdwV16iSSdxhuaxpx67UZz1MobSbc0ggtpYb6MJzgXTr1IYWmGZNNrJWgW/ynhTeS2K4eMU8QnShGImkiwPKdSb4pp/EDLJEtK89C3ZalcloQIDAQAB',
        'private_key' => 'MIIEowIBAAKCAQEAkFCMMz1ykzX74KlINca6lwu+J5tO/a7pL5nF2bsZ9bWv9jaObH0BljCFtMmGXJKzfxYdl18LCiFfTVyTPwV+kHU5Nb7DFAL0UuwmBlU7/lWbcbfy0xFJpAb1ug2ibHbzZhi1cLP4Xkv9FxIVtKth7NQ/mzS/xTREA/0/GFIgcTESOMHLILLQPXWkZdWkSfe9o/gcmKsfVpr4LheFXEaiu1nzRMR7xKWgribeGotYZHWKxZ2ygRDvsBiU6F9DSKLFRBzMNrMgSkz7xa9HpsUYYH6rESzknkXs/IOJdcGhbhw7yXtyWFdshMU+5444bSQTTI+RkeLVV5KT0S+23fx1xQIDAQABAoIBABsYlIpWYjnTq/wtrJo0Ovp7QPPMzFMDUVMXqzdLUv94YpZ2/dzOx8p8R85+/BRLkxose9fdB4SaaXtsA1WU4YOcDubRb21FdLxRDH6p6D7DshDy+cu5djsu91o8jx+QYfjscLy9fceuutFnGt42Q4ek/mBU0iDjxFV/aiZV3TODO+rustmV8Zlxi4NnVM8QLJ0g3+s8yz9UKCGFJE76IXpF9FgNeONhyuoh1FuN7qrOOkr3oawsdcCau8S1TF5mIhQQPnXAfyeH86AZ6bfha9EGMocTtXnHCTx28184G7s/l2fS7i5c7zDvWbnHx3TDBbujRDGcAEBXLIRSbc+lGGECgYEAyKQz9uliZU6YoHyF3Ir2VqMSwMuVxWgoFDtkrYCT1fl3sde86O+JsHiEg5MRzCOXHNYlqHPZk02QXq5vLTdhdsLfnwo6R2FEhV9RngI70/gUnft5j2zswll8tV6Z8gmipD71Acth2etS2Qf6tqzGt3BhknCz99BWHZ3xmGDhQ2kCgYEAuCHYgEOH26ThHVs9l7iTxONdNm8NhMlIkHyl3V5yJo/sT+fdDN//HisXc8cmyGJN0KDO99oIx/FtraKf/LEwpzDH8+Ycqg7P2Z2cjv8TH92lylE3qLaB8XSbmu/TDaEyXUecWMPRhxOO7ZQmMd8aljP+5gq7HJm+Tk66AebWP/0CgYEAoyWyA3IeylWwkb7vcjekuyn60743WL+q51isMtC8ZV9mzruoJpiwk0hMKFjsPdwqwg33rRQWtUp6vCKG0HOZ0uH+x+mfHe+fsfuh+CPE1kWBLj0PyWo+0oHFgA0ejMr87yUqQH1KYdKSGVv9p5CyPPMqw9LU5AsKGycVDlyojxECgYAeu++CQUNdAyFcIA2g1Hs9wuGdI14zStGk5FlfINOW4jEEiWQZMQ5JP9ITTiuICPiKGT1Sm38ZuI+hCuL/b6f6UCcf68nfDMfev/MQ3zyW9g0lPvc2XvhDkD4k12D4Bm78qOZM3qqLjFUwDKlva5jpd1ZVmXbv6C2ern9Xr+sVFQKBgG2fM7S9MQ9oeFnd4nQ3Uk0aASkOXspO0WoFbrT66qdWccX+EAeeLnrUU8uTCMyEMz+DbN1sIhdV/AmACga1Fku6YCKtDem52YeohAGVMti8SCx33KloiJs4881GeCLiqbhjw1qqPWH3KJkeVvvbKTB+rp9tpkn08iMu+5qV3qsY',
        'log' => [
            'file' => storage_path('logs/alipay.log'),
        ]
    ],
    'wechat' => [
        'app_id' => '',
        'mch_id' => '',
        'key' => '',
        'cert_client' => '',
        'cert_key' => '',
        'log' => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ]
];
