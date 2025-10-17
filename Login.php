<!doctype html> 
<?php
session_start();
include "../rate.php"; // make sure this contains your $conn connection
include "LoginStuff.php";
?>

<html> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All ! | Login</title>
    <link rel="icon" type = "image/svg+xml" href="\\cs3-dev.ict.ru.ac.za\practicals\4A2\logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../mainStyle.css"/>
    <link rel="stylesheet" href="login.css"/>
</head>


  <body>
    

      
 <main>
  <!-- 🔐 Login Form -->
    <section id="loginSection">
      <article id="errCheck">
        <?php echo $err; ?>
      </article>

      <form action="Login.php" method="post">
        <h2> Welcome Back! Log In Below</h2>
        <label for="login-email">Email:</label>
        <input type="text" id="login-email" name="login-email" placeholder="you@example.com" required>

        <label for="login-password">Password:</label>
        <input type="password" id="login-password" name="login-password" placeholder="••••••••" required>

        <input type="hidden" name="LogInH" value="L">
        <button type="submit">Log In</button>
      </form>

      <div class="content">
        <p>Don’t have an account?</p>
        <span id="showSignup">Sign up</span>
      </div>
      <div class="guest-option">
        <a href="index.php" id="continueGuest">Continue as Guest</a>
      </div>
    </section>

    <!-- 📝 Sign-Up Form -->
    <section id="signupSection" style="display:none;">
      <form action="Login.php" method="post">
        <h2> New Here? Create an Account!</h2>

        <label for="signup-email">Email:</label>
        <input type="email" id="signup-email" name="signup-email" placeholder="you@example.com" required>

        <label for="signup-Aname">Full Name:</label>
        <input type="text" id="signup-Aname" name="signup-Aname" placeholder="Your Name" required>

        <label for="myDate">Date of Birth:</label>
        <input type="date" id="myDate" name="myDate" required>

        <label for="signup-name">Username:</label>
        <input type="text" id="signup-name" name="signup-name" placeholder="Your username" required>

        <label for="signup-phone">Phone Number:</label>
        <input type="tel" id="signup-phone" name="signup-phone" placeholder="123-456-7890" required>

        <label for="signup-password">Password:</label>
        <input type="password" id="signup-password" name="signup-password" placeholder="Create a password" required>

        <label for="confirm-password">Confirm Password:</label>
        <input type="password" id="confirm-password" name="confirm-password" placeholder="Repeat your password" required>

        <input type="hidden" name="SignUpH" value="S">
        <button type="submit">Sign Me Up!</button>
      </form>

      <div class="content">
        <p>Already have an account?</p>
        <span id="showLogin">Log in</span>
      </div>
      <div class="guest-option">
        <a href="index.php" id="continueGuest">Continue as Guest</a>
      </div>
    </section>
  </main>

  </body>
  <script src="sec.js"></script>
</html>