<?php
session_start();

// Initialize tasks array if it doesn't exist
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $newTask = [
                    'id' => uniqid(),
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'due_date' => $_POST['due_date'],
                    'completed' => false
                ];
                $_SESSION['tasks'][] = $newTask;
                break;
            case 'toggle':
                $taskId = $_POST['task_id'];
                foreach ($_SESSION['tasks'] as &$task) {
                    if ($task['id'] === $taskId) {
                        $task['completed'] = !$task['completed'];
                        break;
                    }
                }
                break;
            case 'edit':
                $taskId = $_POST['task_id'];
                foreach ($_SESSION['tasks'] as &$task) {
                    if ($task['id'] === $taskId) {
                        $task['title'] = $_POST['title'];
                        $task['description'] = $_POST['description'];
                        $task['due_date'] = $_POST['due_date'];
                        break;
                    }
                }
                break;
            case 'delete':
                $taskId = $_POST['task_id'];
                $_SESSION['tasks'] = array_filter($_SESSION['tasks'], function($task) use ($taskId) {
                    return $task['id'] !== $taskId;
                });
                break;
        }
    }
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Sort tasks by due date
usort($_SESSION['tasks'], function($a, $b) {
    return strtotime($a['due_date']) - strtotime($b['due_date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavender Todo App</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8e44ad;
            --secondary-color: #9b59b6;
            --background-color: #f3e5f5;
            --text-color: #4a4a4a;
            --completed-color: #95a5a6;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 2em;
            margin-bottom: 20px;
        }

        .form-container {
            background-color: var(--background-color);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 0;
        }

        input[type="text"],
        input[type="datetime-local"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #fff;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        li:hover {
            transform: translateY(-3px);
        }

        .completed {
            text-decoration: line-through;
            opacity: 0.7;
            background-color: var(--completed-color);
        }

        .task-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .edit-form {
            display: none;
            margin-top: 10px;
        }

        .task-title {
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        .task-description {
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .task-due-date {
            font-size: 0.8em;
            color: #777;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            li {
                padding: 10px;
            }

            .task-actions {
                flex-direction: column;
            }

            button {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-clipboard-list"></i> Lavender Todo App</h1>
        
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="title" placeholder="Task Title" required>
                <textarea name="description" placeholder="Task Description"></textarea>
                <input type="datetime-local" name="due_date" required>
                <button type="submit"><i class="fas fa-plus"></i> Add Task</button>
            </form>
        </div>

        <ul id="task-list">
            <?php foreach ($_SESSION['tasks'] as $task): ?>
                <li data-task-id="<?php echo $task['id']; ?>" class="<?php echo $task['completed'] ? 'completed' : ''; ?>">
                    <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                    <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                    <div class="task-due-date"><i class="far fa-clock"></i> Due: <?php echo date('M j, Y g:i A', strtotime($task['due_date'])); ?></div>
                    <div class="task-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit">
                                <?php echo $task['completed'] ? '<i class="fas fa-undo"></i> Undo' : '<i class="fas fa-check"></i> Complete'; ?>
                            </button>
                        </form>
                        <button onclick="showEditForm('<?php echo $task['id']; ?>')"><i class="fas fa-edit"></i> Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit"><i class="fas fa-trash-alt"></i> Delete</button>
                        </form>
                    </div>
                    <form method="POST" class="edit-form" id="edit-form-<?php echo $task['id']; ?>">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                        <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                        <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea>
                        <input type="datetime-local" name="due_date" value="<?php echo date('Y-m-d\TH:i', strtotime($task['due_date'])); ?>" required>
                        <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        function showEditForm(taskId) {
            const editForm = document.getElementById(`edit-form-${taskId}`);
            editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>