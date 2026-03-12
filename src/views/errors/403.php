<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied - Budgie</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-navy: #22333B;
            --black: #0A0908;
            --brown: #5E503F;
            --tan: #C6AC8E;
            --cream: #EAE0D5;
            --white: #FEFEFE;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--cream) 0%, var(--tan) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .error-container {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(10, 9, 8, 0.15);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            border: 2px solid rgba(198, 172, 142, 0.3);
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            color: var(--dark-navy);
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: var(--brown);
            font-size: 1.125rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--dark-navy) 0%, var(--black) 100%);
            color: var(--cream);
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(34, 51, 59, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(34, 51, 59, 0.4);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <div class="error-code">403</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            <?php 
            echo isset($message) && $message 
                ? htmlspecialchars($message) 
                : 'You do not have permission to access this page. Administrator privileges are required.';
            ?>
        </p>
        <a href="<?php echo BASE_PATH ?? '/'; ?>/public/index.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>
