 <?php
    include"db_config.php";
    $email =$_POST['email'];
    $password = $_POST['password'];
    $sql="select*from user where email='$email' and pwd='$password'";
    $result = mysqli_query($conn,$sql);
    if(mysqli_num_rows ($result)>0 ){
        echo"login success";
    }
    else{
        echo"wrong email or password";

    }
?>
