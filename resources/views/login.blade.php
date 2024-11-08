<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script>
        // Этот код будет выполняться при отправке формы логина
        function login(event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        fetch('https://labs/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username,
                password: password,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.token) {
                // Получаем уже сохраненные токены из localStorage
                let tokens = JSON.parse(localStorage.getItem('auth_tokens')) || [];

                // Проверяем, достигнуто ли максимальное количество токенов
                const maxTokens = data.max_tokens; // Используем значение из ответа
                if (tokens.length >= maxTokens) {
                    alert('Достигнуто максимальное количество активных токенов');
                    return;
                }

                // Проверяем, есть ли уже этот токен
                if (!tokens.includes(data.token)) {
                    // Добавляем новый токен
                    tokens.push(data.token);

                    // Сохраняем обновленный массив токенов в localStorage
                    localStorage.setItem('auth_tokens', JSON.stringify(tokens));
                }
                
                // Проверяем, существует ли refresh_token перед его сохранением
                const existingRefreshToken = localStorage.getItem('refresh_token');
                    if (!existingRefreshToken) {
                        localStorage.setItem('refresh_token', data.refresh_token);
                    }

                alert('Успешная авторизация!');
            } else {
                alert('Ошибка авторизации');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Ошибка при запросе');
        });
    }
    </script>
</head>
<body>
    <h1>Login</h1>
    <form onsubmit="login(event)">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username"><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password"><br><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>