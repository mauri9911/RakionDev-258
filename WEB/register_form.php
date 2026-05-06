<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #323232;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 384px;
            height: 277px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            color: #555;
            text-align: left;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            box-sizing: border-box;
        }

        input[type="submit"] {
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 14px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .error {
            color: #ff0000;
            font-size: 12px;
            text-align: center;
            margin-bottom: 10px;
        }

        .success {
            color: #28a745;
            font-size: 12px;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function validateForm() {
            var id = document.getElementById('id').value;
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (id.length < 4) {
                showMessage("ID must be at least 4 characters long.");
                return false;
            }

            if (!emailPattern.test(email)) {
                showMessage("Please enter a valid email address.");
                return false;
            }

            if (password.length < 6) {
                showMessage("Password must be at least 6 characters long.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Create an Account</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?= htmlspecialchars($error_message) ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="id">User ID:</label>
                <input type="text" id="id" name="id" placeholder="Enter your ID" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>

            <input type="submit" value="Register">
        </form>
        <div class="footer">
            <p>Already have an account? <a href="login.html">Login here</a></p>
        </div>
    </div>
</body>
</html>