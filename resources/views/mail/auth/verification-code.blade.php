<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificação - Minha Saúde</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        /* .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        } */

        .header {
            margin-bottom: 30px;
        }

        h1 {
            color: #1a1a1a;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .greeting {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .message {
            font-size: 16px;
            color: #333;
            margin-bottom: 30px;
        }

        .code-container {
            background-color: #f0f0f0;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px auto;
            width: fit-content;
        }

        .code {
            font-size: 42px;
            font-weight: 700;
            letter-spacing: 8px;
            color: #1a1a1a;
            font-family: 'Courier New', Courier, monospace;
        }

        .expiry-notice {
            font-size: 15px;
            color: #666;
            margin: 25px 0;
            line-height: 1.8;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #888;
        }

        .brand {
            font-weight: 600;
            color: #1a1a1a;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <p class="greeting">Olá,</p>
        </div>

        <div class="message">
            Este é o seu código de verificação único.
        </div>

        <div class="code-wrapper">
            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>
        </div>

        <div class="expiry-notice">
            Este código é válido apenas pelos próximos 15 minutos. Assim que o código expirar, você precisará solicitar
            um novo código.
        </div>

        <div class="footer">
            <p class="brand">Minha Saúde</p>
            <p style="margin-top: 20px; font-size: 13px;">
                Se você não reconhece esta requisição, sinta-se livre para ignorá-la.
            </p>
        </div>
    </div>
</body>

</html>