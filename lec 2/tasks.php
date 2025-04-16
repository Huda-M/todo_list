<?php
include("db_connection.php");
$errors = [];


if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id = $delete_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = mysqli_query($conn, 
        "SELECT tasks.*, users.name AS user_name 
         FROM tasks 
         LEFT JOIN users ON tasks.user_id = users.id 
         WHERE tasks.id = $edit_id"
    );
    $edit_task = mysqli_fetch_assoc($edit_query);
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $priority = (int)$_POST['priority'];
    $user_id = (int)$_POST['user_id'];
    
    if (isset($_POST['create'])) {
        $sql = "INSERT INTO tasks 
               (title, content, priority, user_id, deadline, created_at) 
               VALUES ('$title', '$content', $priority, $user_id, 
               DATE_ADD(NOW(), INTERVAL 1 DAY), NOW())";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['update']) && isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $sql = "UPDATE tasks SET 
               title='$title', 
               content='$content', 
               priority=$priority, 
               user_id=$user_id 
               WHERE id=$id";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}


$tasks_query = mysqli_query($conn, 
    "SELECT tasks.*, users.name AS user_name 
     FROM tasks 
     LEFT JOIN users ON tasks.user_id = users.id"
);


$users = mysqli_query($conn, "SELECT id, name FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($edit_task) ? "Edit Task" : "Add Task" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endforeach; ?>

        <h2><?= isset($edit_task) ? "Edit Task" : "Add Task" ?></h2>
        <form method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" name="title" id="title" 
                       value="<?= isset($edit_task) ? htmlspecialchars($edit_task['title']) : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" name="content" id="content" required><?= isset($edit_task) ? htmlspecialchars($edit_task['content']) : '' ?></textarea>
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <input type="number" class="form-control" name="priority" id="priority" 
                       value="<?= isset($edit_task) ? $edit_task['priority'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">User</label>
                <select class="form-select" name="user_id" id="user_id" required>
                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                        <option value="<?= $user['id'] ?>" 
                            <?= isset($edit_task) && $edit_task['user_id'] == $user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="<?= isset($edit_task) ? 'update' : 'create' ?>" 
                    class="btn <?= isset($edit_task) ? 'btn-warning' : 'btn-primary' ?>">
                <?= isset($edit_task) ? 'Update' : 'Create' ?>
            </button>
        </form>

        <h2 class="mt-5">All Tasks</h2>
        <?php if (mysqli_num_rows($tasks_query) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($tasks_query)): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($row['content']) ?></p>
                        <p class="card-text">Priority: <?= $row['priority'] ?></p>
                        <p class="card-text">User: <?= htmlspecialchars($row['user_name']) ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                <?= date('Y-m-d H:i', strtotime($row['created_at'])) ?>
                            </small>
                        </p>
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">No tasks found</div>
        <?php endif; ?>
    </div>
</body>
</html>