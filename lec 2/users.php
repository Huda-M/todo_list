<?php
session_start();
include_once "validation.php";
include_once "functions.php";
include("db_connection.php");

$errors = [];

if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id");
    setMessage('success', 'User deleted successfully');
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = mysqli_query($conn, 
        "SELECT users.*, users_phone.phone 
         FROM users 
         LEFT JOIN users_phone ON users.id = users_phone.user_id 
         WHERE users.id = $edit_id"
    );
    $edit_user = mysqli_fetch_assoc($edit_query);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);

    $validation_error = validateuser($name, $email, $password, $phone);
    if ($validation_error) {
        setMessage('danger', $validation_error);
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['edit']) ? "?edit=".$_GET['edit'] : ""));
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (isset($_POST['create'])) {
        $sql = "INSERT INTO users (name, email, password) 
                VALUES ('$name', '$email', '$hashed_password')";
        
        if (mysqli_query($conn, $sql)) {
            $user_id = mysqli_insert_id($conn);

            mysqli_query($conn, 
                "INSERT INTO users_phone (user_id, phone) 
                 VALUES ($user_id, '$phone')"
            );
            setMessage('success', 'User added successfully');
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            setMessage('danger', "Error: " . mysqli_error($conn));
        }
    }

    if (isset($_POST['update']) && isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];

        $sql = "UPDATE users SET 
                name='$name', 
                email='$email', 
                password='$hashed_password' 
                WHERE id=$id";
        
        if (mysqli_query($conn, $sql)) {
            mysqli_query($conn, 
                "UPDATE users_phone SET phone='$phone' 
                 WHERE user_id=$id"
            );
            setMessage('success', 'User updated successfully');
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            setMessage('danger', "Error: " . mysqli_error($conn));
        }
    }
}

$users_query = mysqli_query($conn, 
    "SELECT users.*, users_phone.phone 
     FROM users 
     LEFT JOIN users_phone ON users.id = users_phone.user_id"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($edit_user) ? "Edit User" : "Add User" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <?php showMessage(); ?>

    <h2><?= isset($edit_user) ? "Edit User" : "Add User" ?></h2>
    <form method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" name="name" id="name"
                   value="<?= isset($edit_user) ? htmlspecialchars($edit_user['name']) : '' ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email"
                   value="<?= isset($edit_user) ? htmlspecialchars($edit_user['email']) : '' ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" id="phone"
                   value="<?= isset($edit_user) ? htmlspecialchars($edit_user['phone']) : '' ?>" required>
        </div>
        <button type="submit" name="<?= isset($edit_user) ? 'update' : 'create' ?>"
                class="btn <?= isset($edit_user) ? 'btn-warning' : 'btn-primary' ?>">
            <?= isset($edit_user) ? 'Update' : 'Create' ?>
        </button>
    </form>

    <h2 class="mt-5">All Users</h2>
    <?php if (mysqli_num_rows($users_query) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($users_query)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($row['email']) ?></p>
                    <p class="card-text"><?= htmlspecialchars($row['phone']) ?></p>
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No users found</div>
    <?php endif; ?>
</div>
</body>
</html>
