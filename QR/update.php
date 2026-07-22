<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Computer Checks | Update Form</title>
</head>
<body>

<?php 
include'connection.php';
     if (isset($_GET['student_id'])) {
          $student_id = $_GET['student_id'];

          $query = "SELECT * FROM students WHERE student_id = :student_id";

          $statement = $pdo->prepare($query);
          $data = [':student_id'=>$student_id];
          $statement->execute($data);

          $result = $statement->fetch(PDO::FETCH_OBJ);
     }
    if (isset($_POST['update'])) {
          $student_id = $_POST['student_id'];
          $reg_no = $_POST['reg_no'];
          $name = $_POST['name'];
          $address = $_POST['address'];
          $email = $_POST['email'];
          $phone = $_POST['phone'];
          $institution_id = $_POST['institution_id'];
        

          try {
               $query = "UPDATE users SET reg_no=:reg_no, name=:name, address=:address, email=:email, phone=:phone, institution_id=:institution_id WHERE student_id=:student_id ";
               $statement = $pdo->prepare($query);

               $data =[
                    ':reg_no' =>$reg_no,
                    ':name' =>$name,
                    ':address' =>$address,
                    ':email' =>$email,
                    ':phone' =>$phone,
                    ':institution_id' =>$institution_id,
                    'student_id' => $student_id
               ];

               $query_execute = $statement->execute($data);

               if ($query_execute) {
                  ?> 

                    <div class="alert alert-info alert-dismissible fade show d-flex" role="alert">
                      <strong>Data Updated Successfully</strong>
                      
                        <a href="view_students.php" class="btn btn-primary btn-sm ms-auto">View Students</a>
                      <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

    <?php
               }
               else {
                    $_SESSION['message'] = "Data Not Updated....";
                    header("Location:update_students.php ");
                    exit(0);
               }


          } catch (PDOException $e) {
               echo $e->getMessage();
          }
     } 



?>
    <h2>Update Student Data</h2>

    <form action="update_students.php" method="post">
        <!-- Display the current data in form fields -->
        <label for="reg_no">Registration Number:</label>
        <p><input type="text" name="reg_no" value="<?=$result->reg_no ?>" required></p>

        <label for="name">Name:</label>
        <p><input type="text" name="name" value="<?=$result->name ?>" required></p>

        <label for="address">Address:</label>
        <p><input type="text" name="address" value="<?=$result->address ?>" required></p>

        <label for="email">Email:</label>
        <p><input type="email" name="email" value="<?=$result->email ?>" required></p>

        <label for="phone">Phone:</label>
        <p><input type="text" name="phone" value="<?=$result->phone ?>" required></p>

        <label for="institution_id">Institution ID:</label>
        <p><input type="text" name="institution_id" value="<?=$result->institution_id ?>" required></p>

        <!-- Add more fields as needed -->

        <!-- Hidden field to store the user ID for updating -->
        <input type="hidden" name="student_id" value="<?=$result->student_id ?>">

        <button type="submit" name="update">Update Data</button>
    </form>

</body>
</html>
