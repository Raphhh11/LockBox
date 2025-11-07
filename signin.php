<?php
// signin.php
session_start();
if (isset($_SESSION['user_id'])) {
  header('Location: pesan.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In | LockBox</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100">

<!-- Navbar -->
  <nav class="flex items-center justify-between px-10 py-4 bg-white shadow-sm fixed w-full top-0 z-50">
    <div class="flex items-center space-x-2">
      <div class="bg-blue-600 text-white p-2 rounded-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      </div>
      <span class="font-bold text-xl tracking-tight">LockBox</span>
    </div>
  </nav>

  <main class="bg-white shadow-xl rounded-2xl w-96 p-8 relative">
    <!-- Garis gradasi -->
    <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl bg-gradient-to-r from-blue-500 to-pink-400"></div>

    <h2 class="text-2x1 font-bold text-center text-gray-800 mt-4">Welcome Back 🔐</h2>
    <p class="text-center text-gray-500 text-sm mb-8">Get in into your account</p>

    <form action="proses_signin.php" method="POST" class="space-y-5">
      <div>
        <label for="username" class="block text-sm text-gray-600 mb-1">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required
               class="w-full px-4 py-2 border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
      </div>

      <div>
        <label for="password" class="block text-sm text-gray-600 mb-1">Password</label>
        <input type="password" id="password" name="password" placeholder="************" required
               class="w-full px-4 py-2 border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
      </div>

      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded-full font-medium hover:bg-blue-700 transition">
        Log In
      </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
      Don't have any account ?
      <a href="signup.php" class="text-blue-600 hover:underline">Sign Up</a>
    </p>
  </main>

</body>
</html>
