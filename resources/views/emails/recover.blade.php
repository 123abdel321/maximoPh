<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo Electrónico</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
        }
        .header {
            background-color: #075260;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            font-size: 20px;
            color: #333333;
        }

        .content h3 {
            font-size: 15px;
            color: #333333;
        }
        
        .content p {
            font-size: 16px;
            color: #555555;
            line-height: 1.5;
        }
        .content a {
            color: white;
            text-decoration: none;
        }
        .button {
            display: inline-block;
            background-color: #075260;
            color: #ffffff;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 2px 2px 2px rgb(0 0 0 / 57%);
        }
        .footer {
            background-color: #f4f4f4;
            color: #777777;
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .code {
            color: #0a93ac !important;
            font-size: 25px !important;
            width: 200px;
            font-weight: bold;
            letter-spacing: 0.25em;
            margin-top: 0px;
        }
        .center {
            text-align: center;
        }
        .center-2 {
            text-align: -webkit-center !important;
        }
        .color-1 {
            color: #0a93ac !important;
            margin-bottom: 10px;
            margin-top: 30px;
        }

        .texto-peque {
            font-size: 12px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <img src="https://maximoph.com/img/logo_blanco.png" alt="Logo" style="max-width: 200px;">
        </div>

        <!-- Contenido -->
        <div class="content">
            <h2>¡Hola, {{ $nombre }}!</h2>
            <p>
                Hemos recibido una solicitud para cambiar la contraseña de tu cuenta MaximoPH.
            </p>

            <div class="center">
                <p class="color-1"> Para continuar con el proceso, utiliza el siguiente código: </p>
                <p class="code">
                    {{ $code_general }}
                </p>
            </div>

            <p class="center texto-peque">
                El código estará vigente solo por 1 hora.
            </p>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <!-- <p>
                ¿Tienes alguna pregunta? <a href="mailto:soporte@ejemplo.com">Contáctanos</a>.
            </p> -->
            <p>
                © 2024 MaximoPh. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>