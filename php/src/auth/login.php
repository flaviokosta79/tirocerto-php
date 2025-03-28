<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tiro Certo</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Ajuste o caminho do CSS -->
</head>
<body>
    <header>
        <h1>Login</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="register.php">Cadastro</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Acesse sua conta</h2>
        <form action="handle_login.php" method="POST"> <!-- handle_login.php será criado depois -->
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
        <p><a href="forgot_password.php">Esqueceu a senha?</a></p> <!-- forgot_password.php será criado depois -->
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Tiro Certo. Todos os direitos reservados.</p>
    </footer>
</body>
</html>