<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>زینوم لانجویتی</title>
    <!-- Lottie (bodymovin) from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.6/lottie.min.js"></script>
    <!-- Google Fonts for modern typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+Bhaijaan+2:wght@400..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <style>
        /* Global reset and font */
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #000;
            overflow: hidden;
        }

        * {
            font-family: "Baloo Bhaijaan 2", sans-serif;
            direction: rtl;
            box-sizing: border-box;
        }

        /* Lottie background container with object-fit: cover behavior */
        #lottie-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        /* Centered glass effect box */
        .glass-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            max-width: 90%;
            width: 400px;
            color: #fff;
            overflow: hidden;
        }

        .glass-box img {
            max-width: 100px;
            margin-bottom: 1rem;
        }

        .glass-box h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .glass-box p {
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .glass-box button {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            background: #ff5a5f;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }

        .glass-box button:hover {
            background: #ff3b3f;
        }

        .counter-container {
            margin-top: 2rem;
        }

        @media screen and (max-width: 768px) {
            .glass-box {
                background: transparent;
                border-radius: 0;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                width: 100vw;
                max-width: unset;
                color: #fff;
            }
        }
    </style>
</head>

<body>
    <!-- Background Lottie Animation -->
    <div id="lottie-background"></div>

    <!-- Centered Glass Effect Box -->
    <div class="glass-box">
        <img src="" alt="لوگو" id="logo">
        <h1 id="main-text">پیام پیش‌فرض</h1>
        <!-- Counter container (shown only when ok is true) -->
        <div id="counter-container">
            <p>انتقال در <span id="counter">10</span> ثانیه...</p>
        </div>
        <!-- Button to trigger immediate redirection -->
        <button id="redirect-btn">همین حالا برو</button>
    </div>

    <script>
        window.data = <?php echo json_encode($data); ?>;

        /*
          Default data is provided in case window.data isn’t defined.
          window.data should be either:
          {ok:false} or {ok:true, toward:string, text?:string, logo?:string}
        */
        if (typeof window.data === 'undefined') {
            window.data = {
                ok: true,
                toward: 'https://example.com',
                text: 'شما به زودی به صفحه مورد نظر هدایت خواهید شد.',
                logo: ''
            };
        }
        const defaultLogo = "https://dev.zeenome.ir/assets/images/logo.svg";
        const defaultText = 'شما به زودی به صفحه مورد نظر هدایت خواهید شد.';
        const data = window.data;
        const errorText = "خطایی ناشناخته رخ داد!" + data.text;

        document.getElementById("logo").src = (data.logo && data.logo.trim() !== '') ? data.logo : defaultLogo;
        document.getElementById("main-text").innerText = (data.text && data.text.trim() !== '') ? data.text : defaultText;

        if (!data.ok) {
            document.getElementById("counter-container").style.display = "none";
            document.getElementById("redirect-btn").style.display = "none";
            document.getElementById("main-text").innerText = errorText;
            document.getElementById("main-text").style.color = 'gold';
            document.getElementById('lottie-background').style.filter = 'grayscale(100%) brightness(.5)';
        } else {
            let seconds = 10;
            const counterElem = document.getElementById("counter");
            const interval = setInterval(() => {
                seconds--;
                counterElem.innerText = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = data.toward;
                }
            }, 1000);
            document.getElementById("redirect-btn").addEventListener("click", function () {
                window.location.href = data.toward;
            });
        }

        const animContainer = document.getElementById('lottie-background');
        var animationData = { "v": "5.1.11", "fr": 30, "ip": 0, "op": 1800, "w": 1200, "h": 1200, "nm": "inp_bg_02_green", "ddd": 0, "assets": [], "layers": [{ "ddd": 0, "ind": 1, "ty": 4, "nm": "purple_hill", "sr": 1, "ks": { "o": { "a": 0, "k": 100, "ix": 11 }, "r": { "a": 0, "k": 15.66, "ix": 10 }, "p": { "a": 0, "k": [2.4, 105.6, 0], "ix": 2 }, "a": { "a": 0, "k": [0, 0, 0], "ix": 1 }, "s": { "a": 0, "k": [163.5984, 163.5984, 100], "ix": 6 } }, "ao": 0, "shapes": [{ "ty": "gr", "it": [{ "ty": "sr", "sy": 2, "d": 1, "pt": { "a": 0, "k": 5, "ix": 3 }, "p": { "a": 0, "k": [0, 0], "ix": 4 }, "r": { "a": 1, "k": [{ "i": { "x": [0.833], "y": [0.833] }, "o": { "x": [0.167], "y": [0.167] }, "n": ["0p833_0p833_0p167_0p167"], "t": 0, "s": [0], "e": [360] }, { "t": 1800 }], "ix": 5 }, "or": { "a": 0, "k": 383.697, "ix": 7 }, "os": { "a": 0, "k": 30, "ix": 9 }, "ix": 1, "nm": "Polystar Path 1", "mn": "ADBE Vector Shape - Star", "hd": false }, { "ty": "gs", "o": { "a": 0, "k": 100, "ix": 9 }, "w": { "a": 0, "k": 371, "ix": 10 }, "g": { "p": 3, "k": { "a": 0, "k": [0.001, 0, 0.19215686274509805, 0.16470588235294117, 0.5, 0, 0, 0, 1, 0, 0.5882352941176471, 0.4980392156862745], "ix": 8 } }, "s": { "a": 0, "k": [-229.68, 34.334], "ix": 4 }, "e": { "a": 0, "k": [-169.186, 589.962], "ix": 5 }, "t": 1, "lc": 1, "lj": 1, "ml": 4, "nm": "purple_stroke", "mn": "ADBE Vector Graphic - G-Stroke", "hd": false }, { "ty": "rp", "c": { "a": 0, "k": 30, "ix": 1 }, "o": { "a": 0, "k": 0, "ix": 2 }, "m": 1, "ix": 3, "tr": { "ty": "tr", "p": { "a": 0, "k": [-14, 16], "ix": 2 }, "a": { "a": 0, "k": [0, 0], "ix": 1 }, "s": { "a": 0, "k": [100, 100], "ix": 3 }, "r": { "a": 0, "k": 5, "ix": 4 }, "so": { "a": 0, "k": 100, "ix": 5 }, "eo": { "a": 0, "k": 100, "ix": 6 }, "nm": "Transform" }, "nm": "Repeater 1", "mn": "ADBE Vector Filter - Repeater", "hd": false }, { "ty": "tr", "p": { "a": 0, "k": [1542.555, -1153.461], "ix": 2 }, "a": { "a": 0, "k": [0, 0], "ix": 1 }, "s": { "a": 0, "k": [227.912, 227.912], "ix": 3 }, "r": { "a": 0, "k": 2.376, "ix": 6 }, "o": { "a": 0, "k": 100, "ix": 7 }, "sk": { "a": 0, "k": 0, "ix": 4 }, "sa": { "a": 0, "k": 0, "ix": 5 }, "nm": "Transform" }], "nm": "purple", "np": 3, "cix": 2, "ix": 1, "mn": "ADBE Vector Group", "hd": false }], "ip": 0, "op": 1800, "st": 0, "bm": 0 }], "markers": [] };

        lottie.loadAnimation({
            container: animContainer,
            renderer: 'svg',
            loop: true,
            autoplay: true,
            animationData: animationData,
            rendererSettings: {
                preserveAspectRatio: 'xMidYMid slice' // ensures the animation covers the whole area
            }
        });
    </script>
</body>

</html>
