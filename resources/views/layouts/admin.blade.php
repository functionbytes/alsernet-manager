<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Admin Panel</h1>
            <div class="flex gap-4">
                <a href="{{ route('admin.roles.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">
                    👥 Usuarios
                </a>
                <a href="{{ route('admin.roles.mappings') }}" class="hover:bg-blue-700 px-3 py-2 rounded">
                    ⚙️ Configuración
                </a>
                <a href="{{ route('logout') }}" class="hover:bg-blue-700 px-3 py-2 rounded">
                    Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="py-6">
        @yield('content')
    </div>

    <footer class="bg-gray-800 text-white text-center py-4 mt-8">
        <p>&copy; 2024 Sistema Dinámico de Roles. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
