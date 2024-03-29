<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyCarAlert</title>
  <link rel="stylesheet" href="css/style1.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <div class="container">
    <div class="form-box box">

      <?php
        ?>
        <header>MyCarAlert</header>
        <hr>
        <form action="#" method="POST">
        <div class="form-box">
        <button type="button" class="btn" onclick="location.href='login.php'">Login</button>
        <br>
        <button type="button" class="btn" onclick="location.href='signup.php'" style="margin-top: 10px;">Signup</button>
        </form>
      </div>
      <?php
      ?>
  </div>
  <script>
    const toggle = document.querySelector(".toggle"),
      input = document.querySelector(".password");
    toggle.addEventListener("click", () => {
      if (input.type === "password") {
        input.type = "text";
        toggle.classList.replace("fa-eye-slash", "fa-eye");
      } else {
        input.type = "password";
      }
    })
  </script>
</body>

</html>
