<?php
session_start();
require_once dirname(__DIR__, 2) . '/helpers/functions.php';

if (!isLoggedIn()) {
    redirect('/login');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Tracking - KIIT SEVA</title>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="navbar-brand">KIIT SEVA</div>
                <div class="d-flex gap-3">
                    <a href="/dashboard" class="nav-link">Dashboard</a>
                    <a href="/logout" class="nav-link">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>🚌 Vehicle Tracking</h1>
        
        <div class="d-flex gap-3 mb-4">
            <button class="btn btn-primary" onclick="filterRoute('all')">All Routes</button>
            <button class="btn btn-outline-primary" onclick="filterRoute('Campus-15')">Campus-15</button>
            <button class="btn btn-outline-primary" onclick="filterRoute('Campus-17')">Campus-17</button>
            <button class="btn btn-outline-primary" onclick="filterRoute('Campus-25')">Campus-25</button>
        </div>

        <div class="row" id="vehicleContainer">
            <div class="col-4 vehicle-card" data-route="Campus-15">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>🚌 KU-2501</h4>
                            <span class="status-indicator online">🟢 LIVE</span>
                        </div>
                        <p><strong>Route:</strong> Campus-15</p>
                        <p><strong>Driver:</strong> Suresh Das</p>
                        <p><strong>Location:</strong> Near Main Gate</p>
                        <p><strong>ETA:</strong> 5 minutes</p>
                        <p><strong>Capacity:</strong> 25/40</p>
                        <div class="mt-3">
                            <small class="text-muted">Last updated: 2 minutes ago</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4 vehicle-card" data-route="Campus-17">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>🚌 KU-1702</h4>
                            <span class="status-indicator warning">🟡 DELAYED</span>
                        </div>
                        <p><strong>Route:</strong> Campus-17</p>
                        <p><strong>Driver:</strong> Ravi Yadav</p>
                        <p><strong>Location:</strong> Campus Junction</p>
                        <p><strong>ETA:</strong> 12 minutes</p>
                        <p><strong>Capacity:</strong> 18/35</p>
                        <div class="mt-3">
                            <small class="text-muted">Last updated: 8 minutes ago</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4 vehicle-card" data-route="Campus-25">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>🚌 KU-2503</h4>
                            <span class="status-indicator offline">🔴 OFF-DUTY</span>
                        </div>
                        <p><strong>Route:</strong> Campus-25</p>
                        <p><strong>Driver:</strong> Manoj Singh</p>
                        <p><strong>Location:</strong> Maintenance</p>
                        <p><strong>ETA:</strong> Not available</p>
                        <p><strong>Capacity:</strong> 0/42</p>
                        <div class="mt-3">
                            <small class="text-muted">Last updated: 2 hours ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>📍 Live Map (Coming Soon)</h3>
            </div>
            <div class="card-body text-center" style="height: 300px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                <div>
                    <div style="font-size: 4rem; margin-bottom: 20px;">🗺️</div>
                    <h4>Interactive Map</h4>
                    <p class="text-muted">Real-time vehicle positions will be displayed here</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function filterRoute(route) {
            await loadVehicles(route === 'all' ? null : route);
        }

        async function loadVehicles(route = null) {
            try {
                const url = route ? `/api/vehicles?route=${encodeURIComponent(route)}` : '/api/vehicles';
                const res = await fetch(url);
                const json = await res.json();
                const vehicles = json.data || [];
                const container = document.getElementById('vehicleContainer');
                container.innerHTML = '';
                if (vehicles.length === 0) {
                    container.innerHTML = '<div class="col-12"><p>No active vehicles.</p></div>';
                    return;
                }
                vehicles.forEach(v => {
                    const statusClass = v.duty_status === 'ON_DUTY' ? 'online' : (v.duty_status === 'BREAK' ? 'warning' : 'offline');
                    const statusIcon = v.duty_status === 'ON_DUTY' ? '🟢 LIVE' : (v.duty_status === 'BREAK' ? '🟡 BREAK' : '🔴 OFF-DUTY');
                    const card = document.createElement('div');
                    card.className = 'col-4 vehicle-card';
                    card.dataset.route = v.route;
                    card.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4>🚌 ${v.vehicle_number}</h4>
                                    <span class="status-indicator ${statusClass}">${statusIcon}</span>
                                </div>
                                <p><strong>Route:</strong> ${v.route}</p>
                                <p><strong>Driver:</strong> ${v.driver_name}</p>
                                <p><strong>Capacity:</strong> ${v.current_load}/${v.capacity}</p>
                                <div class="mt-3">
                                    <small class="text-muted">Last updated: ${v.last_updated}</small>
                                </div>
                            </div>
                        </div>`;
                    container.appendChild(card);
                });
            } catch (e) {
                console.error('Failed to load vehicles', e);
            }
        }

        // Initial load and auto-refresh
        loadVehicles();
        setInterval(loadVehicles, 30000);

        // Simulate real-time updates
        function updateVehicleStatus() {
            const statusElements = document.querySelectorAll('.status-indicator');
            // This would normally update with real data from server
        }
    </script>
</body>
</html>