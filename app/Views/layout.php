<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? '스터디룸 예약 시스템') ?></title>
    <style>
        body {
            font-family: 'Segoe UI', 'Noto Sans KR', sans-serif;
            background-color: #f3f4f6;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 640px;
            background: white;
            margin: 60px auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
            color: #2b2b2b;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 16px;
            box-sizing: border-box;
            font-size: 15px;
        }

        button {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 10px 16px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: 0.2s;
        }

        button:hover {
            background-color: #4338ca;
        }

        .alert {
            border-radius: 6px;
            padding: 10px 15px;
            margin-top: 15px;
        }
        .alert-info { background-color: #e0f2fe; color: #0369a1; }
        .alert-success { background-color: #dcfce7; color: #166534; }
        .alert-warning { background-color: #fef9c3; color: #854d0e; }
        .alert-error { background-color: #fee2e2; color: #991b1b; }

        .text-center { text-align: center; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .price-box {
            font-size: 16px;
            font-weight: 600;
            color: #111;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        .code-input {
            text-align: center;
            font-size: 18px;
            letter-spacing: 2px;
            padding: 6px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><?= esc($title ?? '스터디룸 예약 시스템') ?></h2>
    <?= $this->renderSection('content') ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</body>
</html>
