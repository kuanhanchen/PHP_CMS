<?php

function redirect($location){

    return header("Location:" . $location);
}


function escape($string) {

    global $connection;

    return mysqli_real_escape_string($connection, trim($string));

}


function set_message($msg){

    if(!$msg) {

        $_SESSION['message']= $msg;

    } else {

        $msg = "";

    }

}


function display_message() {

    if(isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }

}

function users_online() {

    // from scripts.js
    if(isset($_GET['onlineusers'])) {

        global $connection;

        if(!$connection) {

            session_start();

            include("../includes/db.php");

            $session = session_id();
            $time = time();
            $time_out_in_seconds = 05;
            $time_out = $time - $time_out_in_seconds;

            $query = "SELECT * FROM users_online WHERE session = '$session'";
            $send_query = mysqli_query($connection, $query);
            $count = mysqli_num_rows($send_query);

            if($count == NULL) {

                mysqli_query($connection, "INSERT INTO users_online(session, time) VALUES('$session','$time')");

            } else {

                mysqli_query($connection, "UPDATE users_online SET time = '$time' WHERE session = '$session'");

            }

            $users_online_query =  mysqli_query($connection, "SELECT * FROM users_online WHERE time > '$time_out'");
            echo $count_user = mysqli_num_rows($users_online_query);

        }

    } // get request isset()

}
// execute this function here
users_online();

function confirmQuery($result) {
    
    global $connection;

    if(!$result) {
          
        die("QUERY FAILED ." . mysqli_error($connection));
    
    }
    
}



function insert_categories(){
    
    global $connection;

    if(isset($_POST['submit'])){

        $cat_title = $_POST['cat_title'];

        if($cat_title == "" || empty($cat_title)) {
        
             echo "This Field should not be empty";
    
        } else {
            // prepare statement explained in category.php
            $stmt = mysqli_prepare($connection, "INSERT INTO categories(cat_title) VALUES(?) ");

            mysqli_stmt_bind_param($stmt, 's', $cat_title);

            mysqli_stmt_execute($stmt);

            confirmQuery($stmt);
            
        }
      
        mysqli_stmt_close($stmt);

    }

}


function findAllCategories() {
    
    global $connection;

    $query = "SELECT * FROM categories";

    $select_categories = mysqli_query($connection,$query);  

    while($row = mysqli_fetch_assoc($select_categories)) {

        $cat_id = $row['cat_id'];
        $cat_title = $row['cat_title'];

        echo "<tr>";

            //categories_id 
            echo "<td>{$cat_id}</td>";

            //categories_title 
            echo "<td>{$cat_title}</td>";

            //edit this category
            // redirecting to the same page with $_GET['update'] = $cat_id
            //include update_categories.php
            echo "<td><a href='categories.php?edit={$cat_id}' class='btn btn-info'>Edit</a></td>";

            // delete this category
            // redirecting to the same page with $_GET['delete'] = $cat_id
            //function deleteCategories();
            echo "<td><a href='categories.php?delete={$cat_id}' class='btn btn-danger'>Delete</a></td>";

        echo "</tr>";

    }
}


function deleteCategories(){
    
    global $connection;

    if(isset($_GET['delete'])){
    
        $the_cat_id = $_GET['delete'];
        $query = "DELETE FROM categories WHERE cat_id = {$the_cat_id} ";
        $delete_query = mysqli_query($connection, $query);
        
        // after clicking the delete button to send the request for deleting the category, we need to refresh the page to make look work.
        header("Location: categories.php");

    }

}


function UnApprove() {
    global $connection;
    if(isset($_GET['unapprove'])){
    
        $the_comment_id = $_GET['unapprove'];
        
        $query = "UPDATE comments SET comment_status = 'unapproved' WHERE comment_id = $the_comment_id ";
        $unapprove_comment_query = mysqli_query($connection, $query);
        header("Location: comments.php");
        
    }

}

// for admin/posts.php
function is_admin($username) {

    global $connection; 

    $query = "SELECT user_role FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $query);
    confirmQuery($result);

    $row = mysqli_fetch_array($result);

    if($row['user_role'] == 'admin'){

        return true;

    }else {

        return false;
    }

}

// for registration.php
function username_exists($username){

    global $connection;

    $query = "SELECT username FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $query);
    confirmQuery($result);

    if(mysqli_num_rows($result) > 0) {

        return true;

    } else {

        return false;

    }

}

// for registration.php
function email_exists($email){

    global $connection;

    $query = "SELECT user_email FROM users WHERE user_email = '$email'";
    $result = mysqli_query($connection, $query);
    confirmQuery($result);

    if(mysqli_num_rows($result) > 0) {

        return true;

    } else {

        return false;

    }

}


function register_user($username, $email, $password){

    global $connection;

    $username = mysqli_real_escape_string($connection, $username);
    $email    = mysqli_real_escape_string($connection, $email);
    $password = mysqli_real_escape_string($connection, $password);

    $password = password_hash( $password, PASSWORD_BCRYPT, array('cost' => 12));
    
    // registered user is subscriber by default
    $query = "INSERT INTO users (username, user_email, user_password, user_role) ";
    $query .= "VALUES('{$username}','{$email}', '{$password}', 'subscriber' )";
    $register_user_query = mysqli_query($connection, $query);

    confirmQuery($register_user_query);
            
}

function login_user_after_register($username, $password){

    global $connection;

    $username = trim($username);
    $password = trim($password);

    $username = mysqli_real_escape_string($connection, $username);
    $password = mysqli_real_escape_string($connection, $password);
    
    $query = "SELECT * FROM users WHERE username = '{$username}' ";
    $select_user_query = mysqli_query($connection, $query);

    if(!$select_user_query) {

        die("QUERY FAILED". mysqli_error($connection));

    }
    
    while($row = mysqli_fetch_array($select_user_query)) {
          
        $db_user_id = $row['user_id'];
        $db_username = $row['username'];
        $db_user_password = $row['user_password'];
        $db_user_firstname = $row['user_firstname'];
        $db_user_lastname = $row['user_lastname'];
        $db_user_role = $row['user_role'];
      
    } 

    // after encrypting, have to using password_verify() to check input password and encrypted password
    if (password_verify($password,$db_user_password)) {
    // if without encrypting, enable to compare passwords directly
    //if ($password === $db_user_password) {           
        $_SESSION['username'] = $db_username;
        $_SESSION['firstname'] = $db_user_firstname;
        $_SESSION['lastname'] = $db_user_lastname;
        $_SESSION['user_role'] = $db_user_role;
           
        redirect("../cms/index.php");

    } else {

        redirect("../cms/index.php");

    }
        
}

function login_user($username, $password){

    global $connection;

    $username = trim($username);
    $password = trim($password);

    $username = mysqli_real_escape_string($connection, $username);
    $password = mysqli_real_escape_string($connection, $password);
    
    $query = "SELECT * FROM users WHERE username = '{$username}' ";
    $select_user_query = mysqli_query($connection, $query);

    if(!$select_user_query) {

        die("QUERY FAILED". mysqli_error($connection));

    }
    
    while($row = mysqli_fetch_array($select_user_query)) {
          
        $db_user_id = $row['user_id'];
        $db_username = $row['username'];
        $db_user_password = $row['user_password'];
        $db_user_firstname = $row['user_firstname'];
        $db_user_lastname = $row['user_lastname'];
        $db_user_role = $row['user_role'];
      
    } 

    // after encrypting, have to using password_verify() to check input password and encrypted password
    if (password_verify($password,$db_user_password)) {
    // if without encrypting, enable to compare passwords directly
    //if ($password === $db_user_password) {           
        $_SESSION['username'] = $db_username;
        $_SESSION['firstname'] = $db_user_firstname;
        $_SESSION['lastname'] = $db_user_lastname;
        $_SESSION['user_role'] = $db_user_role;
           
        redirect("../index.php");

    } else {

        redirect("../index.php");

    }
        
}

function recordCount($table) {
    
    global $connection;

    $query = "SELECT * FROM " . $table;
    $select_all_post = mysqli_query($connection,$query);
    confirmQuery($select_all_post);

    return mysqli_num_rows($select_all_post);

}

function checkStatus($table, $column, $status) {

    global $connection;

    $query = "SELECT * FROM $table WHERE $column = '$status' ";
    $select_all_post = mysqli_query($connection,$query);
    confirmQuery($select_all_post);

    return mysqli_num_rows($select_all_post);

}