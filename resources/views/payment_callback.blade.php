<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتیجه پرداخت</title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .countdown {
            margin-top: 15px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(isset($data['entity']) && $data['entity']['status'] === 'success')
            <h2 class="success">✅ پرداخت موفق</h2>
            <p>پرداخت شما با موفقیت انجام شد</p>
        @else
            <h2 class="error">❌ پرداخت ناموفق</h2>
            <p>{{ $data['entity']['status_message'] ?? $data['message'] ?? 'خطا در پرداخت' }}</p>
        @endif
        
        @if(isset($data['entity']))
        <div class="amount">
            مبلغ: {{ number_format($data['entity']['amount']) }} تومان
        </div>
        @endif
        
        @php
            $redirectUrl = isset($data['entity']['reservation_id']) 
                ? "/admin/meal-reservations/{$data['entity']['reservation_id']}"
                : "/admin/meal-reservations";
        @endphp
        
        <a href="{{ $redirectUrl }}" class="btn">
            مشاهده رزرو
        </a>
        
        <div class="countdown">
            <span id="countdown">5</span> ثانیه تا انتقال خودکار
        </div>
    </div>

    <script>
        let seconds = 5;
        const countdown = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            seconds--;
            countdown.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = "{{ $redirectUrl }}";
            }
        }, 1000);
    </script>
</body>
</html>