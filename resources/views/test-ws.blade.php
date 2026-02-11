<!DOCTYPE html>
<html lang="es">

<head>
    <title> Test WebSocket</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <h1>Esperando eventos...</h1>
    <ul id="messages"></ul>
    <script type="module">
        // Capturamos el ID de forma segura. 
        // Si es null, imprimimos 'null' para evitar error de sintaxis JS.
        const userId = {{ auth()->id() ?? 'null' }};

        console.log("User ID actual:", userId);

        if (userId) {
            setTimeout(() => {
                window.Echo.private('user.' + userId)
                    .listen('TestEvent', (e) => {
                        console.log('🔒 Mensaje Privado:', e);
                        // ... tu lógica para mostrar el mensaje
                    });
            }, 1000);
        } else {
            console.error("❌ No estás logueado. No puedo suscribirme al canal privado.");
            document.body.innerHTML += "<h2 style='color:red'>Por favor inicia sesión primero</h2>";
        }
    </script>
</body>

</html>