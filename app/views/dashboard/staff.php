<?php
session_start();
require_once dirname(__DIR__, 2) . '/helpers/functions.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'staff') {
    redirect('/login');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - KIIT SEVA</title>
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
            <h1>Driver Dashboard</h1>
            <div class="badge badge-info">Staff - Driver</div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>🚌 Vehicle Control</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <h2>KU-2501</h2>
                            <p class="text-muted">Campus-15 Route</p>
                        </div>
                        
                        <div class="mb-4">
                            <div id="dutyStatus" class="badge badge-danger mb-3" style="font-size: 1.2rem;">OFF DUTY</div>
                            <br>
                            <button id="dutyToggle" class="btn btn-success btn-lg" onclick="toggleDuty()">Start Duty</button>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <h4>5h 30m</h4>
                                <small>Today's Duty</small>
                            </div>
                            <div class="col-6">
                                <h4>127 km</h4>
                                <small>Distance Covered</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>📍 Location Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Current Location:</strong>
                            <p class="text-muted">Main Gate, KIIT Campus</p>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Next Stop:</strong>
                            <p class="text-muted">Campus-15 Hostel Block</p>
                        </div>
                        
                        <div class="mb-3">
                            <strong>GPS Status:</strong>
                            <span class="badge badge-success">Connected</span>
                        </div>
                        
                        <button class="btn btn-primary w-100" onclick="updateLocation()">Update Location</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h3>🗺️ Route Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h4>Campus-15 Route</h4>
                            <p class="text-muted">Main Gate → Hostel Blocks → Academic Buildings → Sports Complex → Main Gate</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-4">
                                <div class="text-center p-3 border rounded">
                                    <h5>🏫 Main Gate</h5>
                                    <small>Start Point</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 border rounded">
                                    <h5>🏠 Hostels</h5>
                                    <small>15 students waiting</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 border rounded">
                                    <h5>🏃 Sports Complex</h5>
                                    <small>8 students waiting</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>📞 Emergency</h4>
                    </div>
                    <div class="card-body text-center">
                        <button class="btn btn-danger w-100 mb-2">Emergency Alert</button>
                        <button class="btn btn-warning w-100 mb-2">Report Issue</button>
                        <button class="btn btn-info w-100">Contact Control</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>📊 Today's Stats</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Trips Completed:</span>
                            <strong>12</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Students Transported:</span>
                            <strong>284</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Fuel Efficiency:</span>
                            <strong>8.5 km/l</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>On-time Performance:</span>
                            <strong>92%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let onDuty = false;

        function toggleDuty() {
            const statusElement = document.getElementById('dutyStatus');
            const toggleButton = document.getElementById('dutyToggle');
            
            if (!onDuty) {
                // Start duty
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        statusElement.textContent = 'ON DUTY';
                        statusElement.className = 'badge badge-success mb-3';
                        toggleButton.textContent = 'End Duty';
                        toggleButton.className = 'btn btn-danger btn-lg';
                        onDuty = true;
                        
                        alert('Duty started! GPS location enabled.');
                    }, function(error) {
                        alert('Please enable GPS location to start duty.');
                    });
                } else {
                    alert('GPS not supported by this browser.');
                }
            } else {
                // End duty
                if (confirm('Are you sure you want to end your duty?')) {
                    statusElement.textContent = 'OFF DUTY';
                    statusElement.className = 'badge badge-danger mb-3';
                    toggleButton.textContent = 'Start Duty';
                    toggleButton.className = 'btn btn-success btn-lg';
                    onDuty = false;
                    
                    alert('Duty ended successfully!');
                }
            }
        }

        function updateLocation() {
            if (!onDuty) {
                alert('Please start duty first to update location.');
                return;
            }
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    alert('Location updated successfully!');
                    console.log('Lat:', position.coords.latitude, 'Lng:', position.coords.longitude);
                });
            } else {
                alert('GPS not supported.');
            }
        }

        // Emergency buttons
        document.querySelector('.btn-danger').addEventListener('click', function() {
            if (confirm('Send emergency alert to control room?')) {
                alert('Emergency alert sent! Control room has been notified.');
            }
        });

        document.querySelector('.btn-warning').addEventListener('click', function() {
            const issue = prompt('Describe the issue:');
            if (issue) {
                alert('Issue reported successfully!');
            }
        });

        document.querySelector('.btn-info').addEventListener('click', function() {
            alert('Connecting to control room...\nPhone: +91-XXX-XXX-XXXX');
        });
    </script>
</body>
</html>