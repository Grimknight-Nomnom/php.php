<?php
session_start();
require 'db_config.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Fetch all schedules for this user
$query = "SELECT id, schedule_date FROM schedules WHERE user_id = ? ORDER BY schedule_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Schedules</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2>Book a New Schedule</h2>
    <form action="add_schedule.php" method="POST" class="mb-5">
        <input type="date" name="schedule_date" required min="<?php echo $today; ?>" class="form-control d-inline w-auto">
        <button type="submit" class="btn btn-primary">Add Schedule</button>
    </form>

    <hr>

    <h2>My Schedule History</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): 
                $isPast = ($row['schedule_date'] < $today);
            ?>
            <tr>
                <td><?php echo $row['schedule_date']; ?></td>
                <td>
                    <?php if ($isPast): ?>
                        <span class="badge bg-secondary">Passed (Locked)</span>
                    <?php else: ?>
                        <span class="badge bg-success">Upcoming</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($isPast): ?>
                        <button class="btn btn-danger btn-sm" disabled title="Cannot delete past records">Delete</button>
                    <?php else: ?>
                        <a href="delete_schedule.php?id=<?php echo $row['id']; ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to cancel this schedule?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>