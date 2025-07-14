<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!--Página que aparece en caso de errores de lógica o de la base de datos-->
    <title>Error 500 - Internal Server Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f4f4f4;
        }
        .error-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h1 { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Error 500</h1>
        <p>Sorry, an internal server error has occurred.</p>
        <p>Our team is working to resolve the issue. Please try again later.</p>
        <a href="/3Shape_project/index.php">Back to Home</a>
    </div>
</body>
</html>