<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لعبة التحدي - رياض عبسي</title>
    <style>
        :root {
            --primary-bg: #0a0e17;
            --accent-blue: #00d2ff;
            --gold: #ffcf33;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--primary-bg);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* واجهة الرصيد */
        #ui-header {
            width: 100%;
            padding: 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.5);
            border-bottom: 2px solid var(--accent-blue);
            font-size: 24px;
            font-weight: bold;
        }

        #balance { color: var(--gold); }

        /* منطقة اللعب */
        #game-container {
            position: relative;
            margin-top: 50px;
            width: 350px;
            height: 500px;
            border: 3px solid var(--accent-blue);
            border-radius: 15px;
            background: linear-gradient(180deg, rgba(10,14,23,1) 0%, rgba(20,30,48,1) 100%);
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.3);
        }

        canvas {
            display: block;
        }

        /* زر اللعب */
        #play-btn {
            margin-top: 30px;
            padding: 15px 50px;
            font-size: 20px;
            font-weight: bold;
            color: white;
            background: linear-gradient(45deg, #00d2ff, #3a7bd5);
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        #play-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px var(--accent-blue);
        }

        #play-btn:disabled {
            background: #444;
            cursor: not-allowed;
        }

    </style>
</head>
<body>

    <div id="ui-header">
        الرصيد: <span id="balance">0</span> JOD
    </div>

    <div id="game-container">
        <canvas id="gameCanvas"></canvas>
    </div>

    <button id="play-btn" onclick="dropBall()">إسقاط الكرة</button>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const balanceElement = document.getElementById('balance');
        const playBtn = document.getElementById('play-btn');

        canvas.width = 350;
        canvas.height = 500;

        let balance = 0;
        let isPlaying = false;

        // إعدادات العوائق (المسامير/المثلثات)
        const rows = 7;
        const pins = [];
        const spacingX = 40;
        const spacingY = 50;

        for (let l = 0; l < rows; l++) {
            const rowPins = l + 2;
            const startX = (canvas.width - (rowPins - 1) * spacingX) / 2;
            for (let i = 0; i < rowPins; i++) {
                pins.push({
                    x: startX + i * spacingX,
                    y: 100 + l * spacingY
                });
            }
        }

        // إعدادات صناديق المضاعفات (Multipliers)
        const multipliers = [
            { label: 'x10', val: 10, color: '#ff4b2b' },
            { label: 'x5', val: 5, color: '#f9d423' },
            { label: 'x2', val: 2, color: '#00d2ff' },
            { label: 'x15', val: 15, color: '#ff4b2b' },
            { label: 'x7', val: 7, color: '#f9d423' }
        ];

        const slotWidth = canvas.width / multipliers.length;

        function drawBoard() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // رسم العوائق
            ctx.fillStyle = "#fff";
            pins.forEach(pin => {
                ctx.beginPath();
                ctx.arc(pin.x, pin.y, 4, 0, Math.PI * 2);
                ctx.fill();
            });

            // رسم صناديق المضاعفات
            multipliers.forEach((m, i) => {
                ctx.fillStyle = m.color;
                ctx.fillRect(i * slotWidth + 5, canvas.height - 40, slotWidth - 10, 35);
                ctx.fillStyle = "#000";
                ctx.font = "bold 14px Arial";
                ctx.textAlign = "center";
                ctx.fillText(m.label, i * slotWidth + slotWidth / 2, canvas.height - 17);
            });
        }

        function dropBall() {
            if (isPlaying) return;
            isPlaying = true;
            playBtn.disabled = true;

            let ballX = canvas.width / 2;
            let ballY = 30;
            let vx = (Math.random() - 0.5) * 2;
            let vy = 0;
            const gravity = 0.25;
            const bounce = 0.5;

            function animate() {
                vy += gravity;
                ballX += vx;
                ballY += vy;

                // التصادم مع المسامير
                pins.forEach(pin => {
                    const dx = ballX - pin.x;
                    const dy = ballY - pin.y;
                    const dist = Math.sqrt(dx*dx + dy*dy);
                    if (dist < 10) {
                        vy *= -bounce;
                        vx += (dx / dist) * 2;
                        ballY = pin.y + (dy / dist) * 10;
                    }
                });

                // الجدران الجانبية
                if (ballX < 10 || ballX > canvas.width - 10) vx *= -1;

                drawBoard();
                
                // رسم الكرة
                ctx.fillStyle = "#ffcf33";
                ctx.beginPath();
                ctx.arc(ballX, ballY, 8, 0, Math.PI * 2);
                ctx.fill();
                ctx.shadowBlur = 10;
                ctx.shadowColor = "#ffcf33";

                if (ballY < canvas.height - 50) {
                    requestAnimationFrame(animate);
                } else {
                    // حساب الجائزة
                    const slotIndex = Math.floor(ballX / slotWidth);
                    const win = multipliers[slotIndex].val;
                    balance += win;
                    balanceElement.innerText = balance;
                    
                    isPlaying = false;
                    playBtn.disabled = false;
                    ctx.shadowBlur = 0;
                }
            }
            animate();
        }

        drawBoard();
    </script>
</body>
</html>
