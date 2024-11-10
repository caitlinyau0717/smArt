<!DOCTYPE html>
<html>
    <head>    
        <title>LOGIN</title>    
        <link rel="stylesheet" type="text/css" href="resources/login.css">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap');
        </style>
    </head>
    <body>     
        <div id="heading">
            <h1>LOGIN</h1>
        </div>
        <form action="login.php" method="post">
            <?php if (isset($_GET['error'])) { ?>            
            <p class="error"><?php echo $_GET['error']; ?></p>        
            <?php } ?>        
            <label>User Name</label>        
            <input type="text" name="uname" placeholder="User Name"><br>        
            <label>Password</label>        
            <input type="password" name="password" placeholder="Password"><br>         
            <button type="submit">Login</button>     
        </form>
    </body>
</html>