<?php
session_start();
require_once dirname(__DIR__, 2) . '/helpers/functions.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'teacher') {
    redirect('/login');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - KIIT SEVA</title>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="navbar-brand">KIIT SEVA</div>
                <div class="d-flex gap-3">
                    <a href="/dashboard" class="nav-link active">Dashboard</a>
                    <a href="/logout" class="nav-link">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Welcome, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>!</h1>
            <div class="badge badge-success">Teacher</div>
        </div>

        <div class="row mb-4">
            <div class="col-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-primary">8</h2>
                        <p>Today's Appointments</p>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-success">24</h2>
                        <p>This Week</p>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-info">4.8</h2>
                        <p>Average Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-warning">12</h2>
                        <p>Pending Reviews</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h3>📅 Today's Schedule</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div>
                                <strong>9:00 AM - 10:00 AM</strong>
                                <p class="mb-0">Rahul Sharma - Project Discussion</p>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-success">Complete</button>
                                <button class="btn btn-sm btn-warning">Reschedule</button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div>
                                <strong>2:00 PM - 3:00 PM</strong>
                                <p class="mb-0">Sneha Singh - Assignment Help</p>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-success">Complete</button>
                                <button class="btn btn-sm btn-warning">Reschedule</button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <div>
                                <strong>4:00 PM - 5:00 PM</strong>
                                <p class="mb-0">Available Slot</p>
                            </div>
                            <div>
                                <span class="badge badge-success">Available</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>⚡ Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary w-100 mb-2">Update Availability</button>
                        <button class="btn btn-outline-primary w-100 mb-2">View All Bookings</button>
                        <button class="btn btn-outline-primary w-100">Download Schedule</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>⭐ Recent Feedback</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>★★★★★</span>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                            <p class="mb-0 small">"Very helpful session on algorithms"</p>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>★★★★☆</span>
                                <small class="text-muted">1 day ago</small>
                            </div>
                            <p class="mb-0 small">"Good explanation of concepts"</p>
                        </div>
                        
                        <button class="btn btn-sm btn-outline-primary w-100">View All Feedback</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-success').forEach(btn => {
            if (btn.textContent === 'Complete') {
                btn.addEventListener('click', function() {
                    if (confirm('Mark this appointment as completed?')) {
                        this.textContent = 'Completed';
                        this.disabled = true;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-secondary');
                    }
                });
            }
        });

        document.querySelectorAll('.btn-warning').forEach(btn => {
            if (btn.textContent === 'Reschedule') {
                btn.addEventListener('click', function() {
                    alert('Reschedule functionality will be available soon!');
                });
            }
        });
    </script>
</body>
</html>