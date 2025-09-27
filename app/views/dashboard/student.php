<?php
session_start();
require_once dirname(__DIR__, 2) . '/helpers/functions.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    redirect('/login');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - KIIT SEVA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }
        
        .navbar {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4a90e2;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-links a:hover {
            color: #4a90e2;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        
        .user-info:hover {
            background: #f8f9fa;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #4a90e2;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .welcome-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .action-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .action-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .action-card p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #357abd;
            transform: translateY(-2px);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .main-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .widget {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .widget h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3rem;
        }
        
        .schedule-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .schedule-item:last-child {
            border-bottom: none;
        }
        
        .schedule-time {
            font-weight: 600;
            color: #4a90e2;
        }
        
        .schedule-details {
            flex: 1;
            margin-left: 15px;
        }
        
        .schedule-teacher {
            font-weight: 500;
            color: #333;
        }
        
        .schedule-subject {
            font-size: 0.9rem;
            color: #666;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4a90e2;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }
        
        .activity-time {
            font-size: 0.9rem;
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 25px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #4a90e2;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">KIIT SEVA</div>
            <div class="nav-links">
                <a href="/dashboard">Dashboard</a>
                <a href="/booking">Book Teacher</a>
                <a href="/tracking">Track Vehicle</a>
                <a href="/feedback">Feedback</a>
            </div>
            <div class="user-menu">
                <div class="user-info" onclick="toggleUserMenu()">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($user['name']); ?></div>
                        <div style="font-size: 0.8rem; color: #666;">Student</div>
                    </div>
                    <span>▼</span>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>!</h1>
            <p>Ready to make the most of your university experience?</p>
        </div>
        
        <div class="quick-actions">
            <div class="action-card" onclick="location.href='/booking'">
                <div class="action-icon">👨🏫</div>
                <h3>Book Teacher</h3>
                <p>Schedule appointments with your professors and get the guidance you need.</p>
                <a href="#" class="btn">Book Now</a>
            </div>
            
            <div class="action-card" onclick="location.href='/tracking'">
                <div class="action-icon">🚌</div>
                <h3>Track Vehicle</h3>
                <p>See real-time locations of campus buses and plan your commute better.</p>
                <a href="#" class="btn">Track Now</a>
            </div>
            
            <div class="action-card" onclick="location.href='/feedback'">
                <div class="action-icon">💬</div>
                <h3>Give Feedback</h3>
                <p>Share your experiences and help improve university services for everyone.</p>
                <a href="#" class="btn">Submit Feedback</a>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="main-content">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">2</div>
                        <div class="stat-label">This Week</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Feedback Given</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">4.8</div>
                        <div class="stat-label">Avg Rating</div>
                    </div>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="widget">
                    <h3>📅 Today's Schedule</h3>
                    <div class="schedule-item">
                        <div class="schedule-time">10:00 AM</div>
                        <div class="schedule-details">
                            <div class="schedule-teacher">Dr. Priya Patel</div>
                            <div class="schedule-subject">Computer Science</div>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">2:00 PM</div>
                        <div class="schedule-details">
                            <div class="schedule-teacher">Prof. Rajesh Gupta</div>
                            <div class="schedule-subject">Mathematics</div>
                        </div>
                    </div>
                </div>
                
                <div class="widget">
                    <h3>🚌 Bus Updates</h3>
                    <div class="activity-item">
                        <div class="activity-icon">🟢</div>
                        <div class="activity-content">
                            <div class="activity-title">Campus-15 Bus</div>
                            <div class="activity-time">Arriving in 5 minutes</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">🟡</div>
                        <div class="activity-content">
                            <div class="activity-title">Campus-17 Bus</div>
                            <div class="activity-time">Delayed by 10 minutes</div>
                        </div>
                    </div>
                </div>
                
                <div class="widget">
                    <h3>📢 Recent Activity</h3>
                    <div class="activity-item">
                        <div class="activity-icon">✅</div>
                        <div class="activity-content">
                            <div class="activity-title">Booking Confirmed</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">⭐</div>
                        <div class="activity-content">
                            <div class="activity-title">Feedback Submitted</div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleUserMenu() {
            alert('User menu functionality will be implemented soon!\n\nOptions:\n- Profile Settings\n- Change Password\n- Logout');
        }
        
        // Add some interactivity
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('h3').textContent;
                console.log(`Clicked on ${title}`);
            });
        });
        
        // Simulate real-time updates
        function updateBusStatus() {
            const busItems = document.querySelectorAll('.activity-item');
            // This would normally fetch real data from the server
            console.log('Updating bus status...');
        }
        
        // Update every 30 seconds
        setInterval(updateBusStatus, 30000);
        
        // Welcome animation
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeSection = document.querySelector('.welcome-section');
            welcomeSection.style.opacity = '0';
            welcomeSection.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                welcomeSection.style.transition = 'all 0.6s ease';
                welcomeSection.style.opacity = '1';
                welcomeSection.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>