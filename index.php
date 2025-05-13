<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EveryPound</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .main-wrapper {
            display: flex;
            align-items: center;
            gap: 40px; /* spacing between image and content */
        }

        .main-wrapper img {
            width: 70vh;
            height: 70vh;
            object-fit: contain;
        }

        .content-wrapper {
            background-color: #ffffff;
            padding: 40px 50px;
            border-radius: 12px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            font-size: 52px;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .tagline {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 30px;
            font-weight: 300;
        }

        .links a {
            text-decoration: none;
            margin: 0 15px;
            padding: 12px 28px;
            color: white;
            background-color: #007bff;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
        }

        .links a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <img src="assets/barchartGraphic.jpg" alt="Bar Chart Designed by slidesgo / Freepik">
        <div class="content-wrapper">
            <h1>EveryPound</h1>
            <p class="tagline">Finances made simple</p>
            <div class="links">
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </div>
</body>
</html>
