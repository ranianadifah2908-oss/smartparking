<?php
session_start();

if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'flame':
            $value = $_GET['value'] ?? 0;
            file_put_contents('flame.txt', $value);
            echo json_encode(['status'=>'ok']);
            break;
        case 'get_status':
            $flame = file_exists('flame.txt') ? file_get_contents('flame.txt') : 0;

            echo json_encode([
                'mqtt_connected' => true,
                'gate_open' => false,
                'lamp_on' => true,
                'flame_detected' => $flame == 1 ? true : false,
                'flame_value' => (int)$flame,
                'buzzer_active' => false,
                'latency' => rand(10, 20) . 'ms'
            ]);
            break;
        case 'toggle_gate':
            echo json_encode(['success' => true, 'status' => 'toggled']);
            break;
        case 'toggle_lamp':
            echo json_encode(['success' => true, 'status' => 'toggled']);
            break;
        case 'toggle_buzzer':
            echo json_encode(['success' => true, 'status' => 'toggled']);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
</head>

<body class="dashboard-page">
    <div id="fireAlert" class="fire-popup">
        <div class="fire-box">
            <h1>🚨 DANGER ALERT 🚨</h1>
            <p>High Flame and Gas Detected</p>
            <button onclick="closeFire()">Close</button>
      </div>
    </div>

    <nav class="navbar">
        <div class="navbar-content">
            <h1>Smart Parking Analytics</h1>
            <a href="login.php" class="admin-box">
            <img src="https://ui-avatars.com/api/?name=Admin&background=ffffff&color=2563eb" alt="">

            <div class="admin-info">
                <small>Administrator</small>
                <span>Admin Login</span>
            </div></a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-grid">
        <div class="hero-panel">
    <div class="hero-overlay"></div>

    <div class="hero-content">
        <h1>Welcome to Smart Parking System</h1>
        <p>
            Real-time parking analytics and intelligent vehicle monitoring dashboard.
        </p>
        <div class="hero-stats">
            <div>
                <span>3</span>
                <small>Total Slots</small>
            </div>
            <div>
                <span>24/7</span>
                <small>Monitoring</small>
            </div>
        </div>
    </div>

</div>

            <div class="right-column">
                <div class="card">
                    <h2 class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                        Parking Slot Occupancy
                    </h2>
                    <div class="occupancy-content">
                        <div class="chart-wrapper">
                            <canvas id="donutChart"></canvas>
                        </div>
                        <div class="occupancy-stats">
                            <div class="stat-card stat-occupied">
                                <p class="stat-label">Occupied</p>
                                <p class="stat-value" id="occupiedSlots">8</p>
                            </div>
                            <div class="stat-card stat-available">
                                <p class="stat-label">Available</p>
                                <p class="stat-value" id="availableSlots">12</p>
                            </div>
                            <div class="stat-card stat-percentage">
                                <p class="stat-label">Occupancy Rate</p>
                                <p class="stat-value" id="occupancyRate">40%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2 class="card-title">Parking Slots Status</h2>
                    <div class="slots-grid" id="slotsGrid"></div>
                </div>
            </div>

        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
