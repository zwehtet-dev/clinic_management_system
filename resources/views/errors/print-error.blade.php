<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            text-align: center;
            background: #f8f9fa;
        }
        
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        
        .error-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 24px;
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .error-actions {
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Print Error</h1>
        <p class="error-message">
            {{ $message ?? 'An error occurred while preparing the invoice for printing.' }}
        </p>
        <p style="font-size: 12px; color: #999;">
            Error Token: {{ $token ?? 'N/A' }}
        </p>
        
        <div class="error-actions">
            <button class="btn btn-primary" onclick="window.history.back()">
                ← Go Back
            </button>
            <button class="btn btn-secondary" onclick="window.close()">
                Close Window
            </button>
        </div>
        
        <div style="margin-top: 30px; font-size: 12px; color: #666;">
            <p>If this error persists, please contact your system administrator.</p>
        </div>
    </div>
</body>
</html>