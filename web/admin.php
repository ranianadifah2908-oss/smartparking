<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}


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
    <link rel="stylesheet" href="css/style.css">
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
            <div class="mqtt-status">
                <div class="status-dot" id="mqttDot"></div>
                <span id="mqttText">MQTT Connected</span>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-grid">
            <div class="left-column">
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
            
            <div class="right-column">
                <div class="card">
                    <h2 class="card-title">Quick Status</h2>
                    <div class="status-controls">
                        <div class="control-item">
                            <div class="control-label">
                                <svg width="20" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M13 4h3a2 2 0 0 1 2 2v14"></path>
                                    <path d="M2 20h3"></path>
                                    <path d="M13 20h9"></path>
                                    <path d="M13 4v16"></path>
                                </svg>
                                <span>Gate</span>
                            </div>
                            <button class="btn-toggle" id="gateBtn" onclick="toggleGate()">Closed</button>
                        </div>

                        <div class="control-item">
                            <div class="control-label">
                                <svg width="20" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path>
                                </svg>
                                <span>Flame</span>
                            </div>
                            <span class="status-badge status-normal" id="flameStatus">Normal</span>
                        </div>

                        <div class="control-item">
                            <div class="control-label">
                                <svg width="20" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                                </svg>
                                <span>Buzzer</span>
                            </div>
                            <button class="btn-toggle" id="buzzerBtn">Inactive</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2 class="card-title">Gate Control</h2>
                    <div class="gate-control">
                        <div class="info-card info-broker">
                            <p class="info-label">MQTT Broker</p>
                            <p class="info-value">broker.emqx.io</p>
                        </div>

                        <div class="info-card info-latency">
                            <p class="info-label">Latency</p>
                            <p class="info-value-large" id="latency">12ms</p>
                        </div>

                        <div class="info-card info-alert">
                            <div class="alert-content">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                                </svg>
                                <div>
                                    <p class="info-label">System Alert</p>
                                    <p class="alert-message">All systems operational</p>
                                </div>
                            </div>
                        </div>

                        <button class="btn-refresh" onclick="refreshStatus()">Refresh Status</button>
                    </div>
                </div>
            </div>

            <div class="environment-grid full-width">
                    <div class="card env-card">
                        <h3>Occupancy History</h3>
                        <canvas id="occupancyChart" height="80"></canvas>
                        <div class="env-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="card env-card">
                        <h3>Car Distance</h3>
                        <div class="bar">  
                            <div id="distanceBar"></div>
                        </div>
                        <p id="distanceText">0 cm</p>
                        <p id="distanceStatus">SAFE</p>
                   </div>
            </div>

        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
