<?php
session_start();
$username = $_SESSION['username'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LockBox | Digital Security Platform</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">

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

    <div class="hidden md:flex space-x-8 font-medium text-sm relative items-center">
      <a href="#" class="text-blue-600">Home</a>


      <div class="relative">
        <button id="pagesBtn" class="flex items-center space-x-1 focus:outline-none select-none">
          <span>Pages</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.187l3.71-3.956a.75.75 0 011.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>

        <div id="pagesMenu" class="hidden absolute bg-white border rounded-xl shadow-md mt-2 w-36">
          <a href="pesan.php" class="block px-4 py-2 hover:bg-gray-50">Message Encryption</a>
          <a href="files.php" class="block px-4 py-2 hover:bg-gray-50">File Encryption</a>
          <a href="stegano_fixed.php" class="block px-4 py-2 hover:bg-gray-50">Steganograph</a>
        </div>
      </div>

    </div>

    <div class="flex space-x-3 items-center">
      <?php if ($username): ?>
        <span class="text-sm text-gray-600">Hi, <b><?= htmlspecialchars($username) ?></b></span>
        <a href="logout.php" class="text-sm bg-gray-100 px-3 py-1.5 rounded-lg hover:bg-gray-200">Logout</a>
      <?php else: ?>
        <a href="signin.php" class="text-sm text-blue-600 hover:underline">Sign In</a>
        <a href="signup.php" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Sign Up</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="flex flex-col md:flex-row items-center justify-between px-10 md:px-20 pt-28 pb-32 bg-gray-50">
    <div class="md:w-1/2 space-y-6">
      <h1 class="text-4xl md:text-5xl font-bold leading-tight">
        Lock<span class="text-blue-600">Box
      </h1>
      <p class="text-gray-500">
        Digital solutions to save and secure all of your data.
      </p>
      </div>
    </div>

    <div class="md:w-1/2 mt-12 md:mt-0 flex justify-center relative">
      <div class="absolute bg-blue-600 rounded-full w-80 h-80 -z-10"></div>
      <img src="https://image.idntimes.com/post/20251020/pexels-sora-shimazaki-5926382_595d920d-36bf-4a35-9236-6d20236edd9e.jpg"
           alt="Hero Image"
           class="rounded-full w-72 h-72 object-cover shadow-lg border-4 border-white">
    </div>
  </section>

  <!-- Features Section -->
  <section class="px-10 md:px-32 pb-32 mt-10 grid grid-cols-1 md:grid-cols-3 gap-6 justify-items-center">
    <div class="bg-white rounded-2xl shadow-md p-6 w-72 text-center hover:shadow-lg transition">
      <div class="flex justify-center mb-4">
        <div class="bg-red-100 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 10c0 3.866-3.582 7-8 7s-8-3.134-8-7 3.582-7 8-7 8 3.134 8 7z" />
          </svg>
        </div>
      </div>
      <h3 class="font-semibold text-lg mb-1">24/7 Support</h3>
      <p class="text-gray-500 text-sm leading-relaxed">Ready to help you everytime</p>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 w-72 text-center hover:shadow-lg transition">
      <div class="flex justify-center mb-4">
        <div class="bg-green-100 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2l4-4m5 2a9 9 0 11-18 0a9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <h3 class="font-semibold text-lg mb-1">Take Ownership</h3>
      <p class="text-gray-500 text-sm leading-relaxed">Your request done with full sense of responsibility.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 w-72 text-center hover:shadow-lg transition">
      <div class="flex justify-center mb-4">
        <div class="bg-orange-100 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.105 0-2 .672-2 1.5S10.895 11 12 11s2-.672 2-1.5S13.105 8 12 8zM5.121 17.804a4.992 4.992 0 017.758-5.808a4.992 4.992 0 017.758 5.808A8.978 8.978 0 0112 21a8.978 8.978 0 01-6.879-3.196z" />
          </svg>
        </div>
      </div>
      <h3 class="font-semibold text-lg mb-1">Diversity</h3>
      <p class="text-gray-500 text-sm leading-relaxed">Doing many type of request.</p>
    </div>
  </section>

  <script>
    const btn = document.getElementById('pagesBtn');
    const menu = document.getElementById('pagesMenu');

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      menu.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
      if (!btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.add('hidden');
      }
    });
  </script>

</body>
</html>
