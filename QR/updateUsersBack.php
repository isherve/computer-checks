<?php
include 'connection.php';
if (isset($_POST['update'])) {
    $user_type = $_POST['user_type'];
    $nid = $_POST['nid'];
    $names = $_POST['names'];
    $email = $_POST['email'];

    try {
         $query = "UPDATE users SET user_type=:user_type, names=:names, email=:email WHERE nid=:nid ";
         $statement = $pdo->prepare($query);

         $data =[
              ':nid' =>$nid,
              ':names' =>$names,
              ':email' =>$email,
              'user_type' => $user_type
         ];

         $query_execute = $statement->execute($data);

         if ($query_execute) {
          
            ?> 
             
              <div class="alert alert-info alert-dismissible fade show d-flex" role="alert">
                <strong>Data Updated Successfully</strong>
            
                 <?php 
                    header('Location:view-users.php');a
                 ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>

<?php
        
       }
         else {
              $_SESSION['message'] = "Data Not Updated....";
              header("Location:update-users.php ");
              exit(0);
         }


    } catch (PDOException $e) {
         echo $e->getMessage();
    }
} 

?>