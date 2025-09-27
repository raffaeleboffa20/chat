<?php
session_start();
if (!isset($_SESSION['username'])) {
    if (isset($_GET['username']) && !empty(trim($_GET['username']))) {
        $_SESSION['username'] = htmlspecialchars(trim($_GET['username']));
    } else {
        echo '<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<title>Login Chat LAN</title>
<style>
    body {
        background: linear-gradient(135deg, #667eea, #764ba2);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: white;
        margin: 0;
    }
    form {
        background: rgba(0,0,0,0.4);
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        text-align: center;
        width: 320px;
    }
    input[type="text"] {
        width: 100%;
        padding: 12px 15px;
        margin-top: 15px;
        border-radius: 8px;
        border: none;
        font-size: 16px;
    }
    button {
        margin-top: 20px;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        background-color: #5a67d8;
        color: white;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #434190;
    }
</style>
</head>
<body>
    <form method="GET">
        <h2>Inserisci username</h2>
        <input type="text" name="username" placeholder="Il tuo nome" required autofocus>
        <button type="submit">Entra</button>
    </form>
</body>
</html>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Chat LAN PHP</title>
    <style>
        /* Reset */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fb;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .chat-container {
            background: white;
            width: 600px;
            height: 600px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        header {
            background: #5a67d8;
            padding: 20px;
            color: white;
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            user-select: none;
        }
        #chat-box {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            background: #e9ecef;
        }
        .message {
            margin-bottom: 15px;
            padding: 12px 18px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 80%;
            word-wrap: break-word;
            position: relative;
            font-size: 15px;
        }
        .message .username {
            font-weight: 700;
            color: #4a4a4a;
            margin-bottom: 5px;
            display: block;
        }
        .message .time {
            position: absolute;
            bottom: 5px;
            right: 12px;
            font-size: 11px;
            color: #999;
        }
        /* Differenzio messaggi utente */
        .message.self {
            background: #5a67d8;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }
        .message.self .username {
            color: #dcdcff;
        }
        /* Form input */
        form#chat-form {
            display: flex;
            padding: 15px 20px;
            background: #f1f3f5;
            border-top: 1px solid #ddd;
        }
        #message {
            flex-grow: 1;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 16px;
            outline: none;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
        }
        button[type="submit"] {
            background: #5a67d8;
            color: white;
            border: none;
            margin-left: 15px;
            padding: 0 20px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.25s ease;
        }
        button[type="submit"]:hover {
            background: #434190;
        }
        .file-input-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .file-input-wrapper input[type="file"] {
            /* Nasconde l'input reale ma è cliccabile tramite label */
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: 0;
            overflow: hidden;
            clip: rect(0 0 0 0);
            border: 0;
        }

        .custom-file-label {
            display: inline-block;
            padding: 8px 15px;
            background-color: #5a67d8;
            color: white;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .custom-file-label:hover {
            background-color: #434190;
        }

        .file-name {
            font-size: 14px;
            color: #555;
            font-style: italic;
        }

    </style>
</head>
<body>
    <div class="chat-container">
        <header>Benvenuto, <?php echo $_SESSION['username']; ?>!</header>

        <div id="chat-box"></div>

        <form id="chat-form" autocomplete="off" enctype="multipart/form-data">
            <input type="text" id="message" placeholder="Scrivi un messaggio..." />

            <div class="file-input-wrapper">
                <input type="file" id="image" accept="image/*" />
                <label for="image" class="custom-file-label">
                    Scegli immagine
                </label>
                <span class="file-name">Nessuna immagine selezionata</span>
            </div>

            <button type="submit">Invia</button>
        </form>


    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message');
        const username = '<?php echo addslashes($_SESSION['username']); ?>';

        function loadMessages() {
            fetch('get_messages.php')
                .then(response => response.json())
                .then(data => {
                    chatBox.innerHTML = '';
                    data.forEach(msg => {
                        const div = document.createElement('div');
                        div.classList.add('message');
                        if (msg.username === username) div.classList.add('self');
                        div.innerHTML = `<span class="username">${msg.username}</span>${msg.message}<span class="time">${msg.created_at}</span>`;
                        chatBox.appendChild(div);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        setInterval(loadMessages, 2000);
        loadMessages();

        chatForm.addEventListener('submit', e => {
            e.preventDefault();

            const msg = messageInput.value.trim();
            const imageFile = document.getElementById('image').files[0];

            const formData = new FormData();
            formData.append('message', msg);
            if (imageFile) {
                formData.append('image', imageFile);
            }

            fetch('send_message.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                messageInput.value = '';
                document.getElementById('image').value = '';
                loadMessages();
            });
        });

        const imageInput = document.getElementById('image');
        const fileNameSpan = document.querySelector('.file-name');

        imageInput.addEventListener('change', () => {
            if (imageInput.files && imageInput.files.length > 0) {
                fileNameSpan.textContent = imageInput.files[0].name;
            } else {
                fileNameSpan.textContent = 'Nessuna immagine selezionata';
            }
        });

    </script>
</body>
</html>
