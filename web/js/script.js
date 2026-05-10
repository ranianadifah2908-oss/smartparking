let gateOpen = false;
let lampOn = true;
let buzzerActive = false;

let historyLabels = [];
let historyData = [];
let occupancyChart;

let deviceOnline = false;
let lastMessageTime = 0;

const maxDataPoints = 10;

let slots = {
    A1: 'available',
    A2: 'available',
    A3: 'available'
};

let donutChart;

let lastPing = Date.now();

const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt');

client.on('connect', () => {
    console.log('✅ MQTT Connected');

    client.subscribe('infra/1');
    client.subscribe('infra/2');
    client.subscribe('infra/3');
    client.subscribe('esp2/ultrasonic');
    client.subscribe('esp2/flame');
});

client.on('message', (topic, message) => {
    lastMessageTime = Date.now();
    deviceOnline = true;

    const val = message.toString();
    const now = Date.now();
    const ping = now - lastPing;
    

    document.getElementById('latency').textContent =
    ping + 'ms';

    lastPing = now;

    console.log('📡', topic, val);

    if (topic === 'infra/1') {
        slots.A1 = val == "0" ? 'occupied' : 'available';
    }
    if (topic === 'infra/2') {
        slots.A2 = val == "0" ? 'occupied' : 'available';
    }
    if (topic === 'infra/3') {
        slots.A3 = val == "0" ? 'occupied' : 'available';
    }

    if (topic === 'esp2/ultrasonic') {
        document.getElementById('distanceText').textContent = val + ' cm';

        let percent = Math.min((val / 100) * 100, 100);
        document.getElementById('distanceBar').style.width = percent + '%';
    }

    if (topic === 'esp2/flame') {
        const popup = document.getElementById('fireAlert');
        const flameStatus = document.getElementById('flameStatus');
        const buzzerBtn = document.getElementById('buzzerBtn');
        const alertMessage = document.querySelector('.alert-message');

        if (val === 'API') {
            popup.style.display = 'flex';
            flameStatus.textContent = 'Detected';
            flameStatus.classList.remove('status-normal');
            flameStatus.classList.add('status-detected');

            buzzerBtn.textContent = 'Active';
            buzzerBtn.classList.add('buzzer-active');

            alertMessage.textContent = 'Fire detected in parking area';

        } else {
            popup.style.display = 'none';
            flameStatus.textContent = 'Normal';
            flameStatus.classList.remove('status-detected');
            flameStatus.classList.add('status-normal');

            buzzerBtn.textContent = 'Inactive';
            buzzerBtn.classList.remove('buzzer-active');

            alertMessage.textContent = 'All systems operational';
        }
    }

    renderSlots();
    updateStats();
});

document.addEventListener('DOMContentLoaded', function() {
    renderSlots();
    updateStats();
    initDonutChart();
    initHistoryChart();
});

function initDonutChart() {
    const ctx = document.getElementById('donutChart');
    if (!ctx) return;

    donutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Available'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['#f16666', '#7ddc9e'],
                borderColor: ['#de5252', '#8bbf9d'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function renderSlots() {
    const grid = document.getElementById('slotsGrid');
    if (!grid) return;

    grid.innerHTML = '';

    Object.keys(slots).forEach(id => {
        const div = document.createElement('div');
        div.className = `slot ${slots[id]}`;
        div.textContent = id;
        grid.appendChild(div);
    });
}

function updateStats() {
    const values = Object.values(slots);

    const occupied = values.filter(v => v === 'occupied').length;
    const available = values.filter(v => v === 'available').length;
    const total = values.length;
    const percentage = total ? ((occupied / total) * 100).toFixed(1) : 0;

    const occupiedEl = document.getElementById('occupiedSlots');
    const availableEl = document.getElementById('availableSlots');
    const rateEl = document.getElementById('occupancyRate');

    const now = new Date().toLocaleTimeString();

        historyLabels.push(now);
        historyData.push(parseFloat(percentage));

    if (historyLabels.length > maxDataPoints) {
        historyLabels.shift();
        historyData.shift();
    }

    if (occupancyChart) {
        occupancyChart.update();
}

    if (occupiedEl) occupiedEl.textContent = occupied;
    if (availableEl) availableEl.textContent = available;
    if (rateEl) rateEl.textContent = percentage + '%';
    if (donutChart) {
    donutChart.data.datasets[0].data = [occupied, available];
    donutChart.update();
}
}

function toggleGate() {
    gateOpen = !gateOpen;
    const btn = document.getElementById('gateBtn');

    if (gateOpen) {
        btn.textContent = 'Open';
        btn.classList.add('gate-open');
    } else {
        btn.textContent = 'Closed';
        btn.classList.remove('gate-open');
    }
}

function toggleLamp() {
    lampOn = !lampOn;
    const btn = document.getElementById('lampBtn');

    if (lampOn) {
        btn.textContent = 'ON';
        btn.classList.add('lamp-on');
    } else {
        btn.textContent = 'OFF';
        btn.classList.remove('lamp-on');
    }
}

function updateDistance(jarak) {
    const bar = document.getElementById('distanceBar');
    const text = document.getElementById('distanceText');
    const status = document.getElementById('distanceStatus');

    jarak = parseFloat(jarak);

    let percent = Math.min((jarak / 100) * 100, 100);

    bar.style.width = percent + '%';
    text.textContent = jarak + ' cm';

    if (jarak < 10) {
        status.textContent = 'DANGER';
        bar.style.background = '#ef4444';
    } else if (jarak < 30) {
        status.textContent = 'WARNING';
        bar.style.background = '#f59e0b';
    } else {
        status.textContent = 'SAFE';
        bar.style.background = '#10b981';
    }
}

function updateFlame(val) {
    const popup = document.getElementById('fireAlert');

    if (val === 'API') {
        popup.style.display = 'flex';
    } else {
        popup.style.display = 'none';
    }
}

function initHistoryChart() {
    const ctx = document.getElementById('occupancyChart');
    if (!ctx) return;

    occupancyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: historyLabels,
            datasets: [{
                label: 'Occupancy (%)',
                data: historyData,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100
                }
            }
        }
    });
}

function controlServo2(state) {
    client.publish('servo2/control', state);
}

function toggleGate() {
    gateOpen = !gateOpen;

    const btn = document.getElementById('gateBtn');

    if (gateOpen) {
        btn.textContent = 'Open';
        btn.classList.add('gate-open');

        client.publish('servo2/control', 'ON'); // ⬅️ INI
    } else {
        btn.textContent = 'Closed';
        btn.classList.remove('gate-open');

        client.publish('servo2/control', 'OFF'); // ⬅️ INI
    }
}

function refreshStatus() {

    const latency = document.getElementById('latency');

    latency.textContent =
        Math.floor(Math.random() * 10 + 10) + 'ms';

    renderSlots();
    updateStats();
}

setInterval(() => {

    const mqttDot = document.getElementById('mqttDot');
    const mqttText = document.getElementById('mqttText');

    // kalau lebih dari 5 detik ga ada data dari ESP
    if (Date.now() - lastMessageTime > 5000) {

        deviceOnline = false;

        mqttDot.style.backgroundColor = '#ef4444';
        mqttText.textContent = 'Device Offline';

    } else {

        mqttDot.style.backgroundColor = '#10b981';
        mqttText.textContent = 'MQTT Connected';
    }

}, 1000);

client.on('connect', () => {
    const mqttDot = document.getElementById('mqttDot');
    const mqttText = document.getElementById('mqttText');

    if (mqttDot && mqttText) {
        mqttDot.style.backgroundColor = '#10b981';
        mqttText.textContent = 'MQTT Connected';
    }
});

client.on('offline', () => {
    const mqttDot = document.getElementById('mqttDot');
    const mqttText = document.getElementById('mqttText');

    if (mqttDot && mqttText) {
        mqttDot.style.backgroundColor = '#ef4444';
        mqttText.textContent = 'MQTT Disconnected';
    }
});