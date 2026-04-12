 <?php
    include"my_db.php";
    $email =$_POST['email'];
    $password = $_POST['password'];
    $sql="select*from user where email='$email' and password='$password'";
    $result = mysqli_qurey($conn,$sql);
    if(mysql_num_rows ($result)>0 ){
        echo"login success";
    }
    else{
        echo"wrong email or password";

    }
?>
