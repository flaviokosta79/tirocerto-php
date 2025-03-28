<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Tiro Certo</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Ajuste o caminho do CSS -->
</head>
<body>
    <header>
        <h1>Cadastro</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Crie sua conta</h2>
        <form action="handle_register.php" method="POST"> <!-- handle_register.php será criado depois -->
            <div>
                <label for="name">Nome:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirmar Senha:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Cadastrar</button>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Tiro Certo. Todos os direitos reservados.</p>
    </footer>
</body>
</html>