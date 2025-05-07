<?php
session_start(); // Asegúrate de que esta línea sea la primera

ob_start(); // Inicia el buffer de salida
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tallarín Carwash</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos generales */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Contenedor principal */
        .login-container {
            width: 350px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* Encabezado */
        .login-header {
            background: #2c3e50;
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }

        .login-header h1 {
            margin: 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-header h1 i {
            margin-right: 10px;
            color: #f39c12;
        }

        .logo {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
            background: #f39c12;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        /* Formulario */
        .login-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            background-color: white;
        }

        /* Botón */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #3498db, #2980b9);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: linear-gradient(to right, #2980b9, #3498db);
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Mensajes de error */
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            padding: 10px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
            display: none;
        }

        /* Efectos adicionales */
        .water-drop {
            position: absolute;
            width: 10px;
            height: 10px;
            background: rgba(52, 152, 219, 0.5);
            border-radius: 50%;
            animation: drop 2s linear infinite;
        }

        @keyframes drop {
            0% { transform: translateY(-20px) scale(0.5); opacity: 0; }
            50% { opacity: 0.8; }
            100% { transform: translateY(20px) scale(1.2); opacity: 0; }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-car"></i>
            </div>
            <h1><i class="fas fa-motorcycle"></i> CARWASH MACA</h1>
        </div>
        
        <form class="login-form" action="index.php" method="post">
            <div class="form-group">
                <label for="username">Usuario</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Ingrese su usuario" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Ingrese su contraseña" required>
                </div>
            </div>
            
            <button type="submit" class="btn-login">INGRESAR <i class="fas fa-sign-in-alt"></i></button>
            
            <?php
            
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Configuración de la base de datos
                $host = "localhost";
                $user = "root";
                $password = "admin";
                $database = "sistema-posventa";

                // Conexión a la base de datos
                $conn = new mysqli($host, $user, $password, $database);
                if ($conn->connect_error) {
                    die("Conexión fallida: " . $conn->connect_error);
                }

                $username = $_POST['username'];
                $password = $_POST['password'];

                $query = "SELECT * FROM usuarios WHERE usuario = ? AND estado = 1";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['contrasena'])) {
                      
                        $_SESSION['id_usuario'] = $user['id_usuario'];
                        $_SESSION['nombre'] = $user['nombre'];
                        $_SESSION['rol'] = $user['rol'];

                        header("Location: ../index.php");
                        exit();
                    } else {
                        echo '<div class="error-message">Contraseña incorrecta.</div>';
                    }
                } else {
                    echo '<div class="error-message">El usuario no existe o está desactivado.</div>';
                }

                $stmt->close();
                $conn->close();
            }
            ?>
        </form>
    </div>

    <script>
        // Mostrar mensajes de error con animación
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                if (msg.textContent.trim() !== '') {
                    msg.style.display = 'block';
                    setTimeout(() => {
                        msg.style.opacity = '1';
                    }, 100);
                }
            });

            // Efectos de agua decorativos
            const loginContainer = document.querySelector('.login-container');
            for (let i = 0; i < 5; i++) {
                const drop = document.createElement('div');
                drop.classList.add('water-drop');
                drop.style.left = Math.random() * 100 + '%';
                drop.style.animationDelay = Math.random() * 2 + 's';
                loginContainer.appendChild(drop);
            }
        });
    </script>
</body>
</html>
<?php
ob_end_flush(); // Enviar la salida acumulada
?>