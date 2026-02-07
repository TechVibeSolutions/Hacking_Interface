<?php
// admin.php - Password-protected admin panel

session_start();

// Simple password protection
$password = "admin123";
$isLoggedIn = false;

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $isLoggedIn = true;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $loginError = "Invalid password!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hacking_interface_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all users from database
$users = [];
if ($isLoggedIn) {
    $sql = "SELECT * FROM users ORDER BY submission_time DESC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

// Get statistics
$totalUsers = count($users);
$today = date('Y-m-d');
$todayCount = 0;
$lastWeekCount = 0;
$lastWeek = date('Y-m-d', strtotime('-7 days'));

foreach ($users as $user) {
    if (substr($user['submission_time'], 0, 10) === $today) {
        $todayCount++;
    }
    if (substr($user['submission_time'], 0, 10) >= $lastWeek) {
        $lastWeekCount++;
    }
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Neural Security Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Orbitron:wght@400;700&display=swap');
        
        :root {
            --neon-blue: #0088ff;
            --dark-bg: #0a0a0a;
            --card-bg: #111111;
        }
        
        body {
            font-family: 'Share Tech Mono', monospace;
            background-color: var(--dark-bg);
            color: #e0e0e0;
            min-height: 100vh;
        }
        
        .header-font {
            font-family: 'Orbitron', sans-serif;
        }
        
        .terminal-text {
            font-family: 'Share Tech Mono', monospace;
        }
        
        .neon-border {
            border: 1px solid var(--neon-blue);
            box-shadow: 0 0 10px rgba(0, 136, 255, 0.3);
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            border: 1px solid #333;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 136, 255, 0.2);
        }
        
        .input-field {
            background-color: #1a1a1a;
            border: 1px solid #333;
            color: #e0e0e0;
            padding: 12px;
            border-radius: 6px;
            width: 100%;
            margin-bottom: 15px;
            font-family: 'Share Tech Mono', monospace;
        }
        
        .input-field:focus {
            border-color: var(--neon-blue);
            outline: none;
            box-shadow: 0 0 5px rgba(0, 136, 255, 0.5);
        }
        
        .btn-primary {
            background-color: var(--neon-blue);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            width: 100%;
            font-family: 'Share Tech Mono', monospace;
        }
        
        .btn-primary:hover {
            background-color: #0066cc;
            box-shadow: 0 0 10px rgba(0, 136, 255, 0.5);
        }
        
        .btn-logout {
            background-color: #ff3333;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background-color: #cc0000;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1a1a1a, #222222);
            border-left: 4px solid var(--neon-blue);
        }
        
        .table-row:hover {
            background-color: #222222;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background-color: rgba(0, 255, 65, 0.1);
            color: #00ff41;
            border: 1px solid rgba(0, 255, 65, 0.3);
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 50px 20px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
    <!-- Login Form -->
    <div class="login-container p-8 bg-black bg-opacity-80">
        <div class="text-center mb-8">
            <i class="fas fa-user-shield text-5xl mb-4" style="color: var(--neon-blue);"></i>
            <h1 class="header-font text-3xl mb-2">NEURAL SECURITY INTERFACE</h1>
            <h2 class="terminal-text text-xl mb-6">ADMIN ACCESS PANEL</h2>
            <p class="text-gray-400 mb-6">> Secure login required</p>
        </div>
        
        <?php if (isset($loginError)): ?>
        <div class="mb-6 p-3 bg-red-900 border border-red-700 rounded text-red-200">
            <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($loginError); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-6">
                <label class="block mb-2 terminal-text">
                    <i class="fas fa-lock mr-2"></i> ADMIN PASSWORD
                </label>
                <input type="password" name="password" class="input-field" placeholder="Enter admin password" required autofocus>
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt mr-2"></i> ACCESS ADMIN PANEL
            </button>
        </form>
        
        <div class="mt-8 pt-6 border-t border-gray-800 text-center">
            <a href="index.html" class="text-gray-400 hover:text-gray-300 terminal-text text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Main Interface
            </a>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Admin Dashboard -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-center mb-10">
            <div>
                <h1 class="header-font text-3xl mb-2">NEURAL SECURITY INTERFACE</h1>
                <h2 class="terminal-text text-xl text-gray-400">ADMIN DASHBOARD</h2>
            </div>
            
            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                <div class="p-2 bg-green-900 bg-opacity-30 rounded">
                    <i class="fas fa-circle text-green-500 mr-2"></i>
                    <span class="terminal-text">SYSTEM ACTIVE</span>
                </div>
                <a href="?logout=1" class="btn-logout">
                    <i class="fas fa-sign-out-alt mr-2"></i> LOGOUT
                </a>
            </div>
        </header>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="stat-card p-6 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-400 terminal-text">Total Scans</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $totalUsers; ?></h3>
                    </div>
                    <i class="fas fa-database text-3xl opacity-50" style="color: var(--neon-blue);"></i>
                </div>
            </div>
            
            <div class="stat-card p-6 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-400 terminal-text">Today's Scans</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $todayCount; ?></h3>
                    </div>
                    <i class="fas fa-calendar-day text-3xl opacity-50" style="color: var(--neon-blue);"></i>
                </div>
            </div>
            
            <div class="stat-card p-6 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-400 terminal-text">Last 7 Days</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $lastWeekCount; ?></h3>
                    </div>
                    <i class="fas fa-chart-line text-3xl opacity-50" style="color: var(--neon-blue);"></i>
                </div>
            </div>
            
            <div class="stat-card p-6 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-400 terminal-text">Last Scan</p>
                        <h3 class="text-xl font-bold mt-2 terminal-text">
                            <?php echo $totalUsers > 0 ? date('H:i', strtotime($users[0]['submission_time'])) : 'N/A'; ?>
                        </h3>
                    </div>
                    <i class="fas fa-clock text-3xl opacity-50" style="color: var(--neon-blue);"></i>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="neon-border rounded-xl p-6 mb-10 bg-black bg-opacity-80">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <h3 class="header-font text-xl mb-4 md:mb-0">SCAN RECORDS</h3>
                <div class="terminal-text text-gray-400 text-sm">
                    Last updated: <?php echo date('Y-m-d H:i:s'); ?>
                    <a href="admin.php" class="ml-4 text-blue-400 hover:text-blue-300">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </a>
                </div>
            </div>
            
            <?php if ($totalUsers > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-900">
                            <th class="py-3 px-4 text-left terminal-text">ID</th>
                            <th class="py-3 px-4 text-left terminal-text">Name</th>
                            <th class="py-3 px-4 text-left terminal-text">Fingerprint ID</th>
                            <th class="py-3 px-4 text-left terminal-text">IP Address</th>
                            <th class="py-3 px-4 text-left terminal-text">Submission Time</th>
                            <th class="py-3 px-4 text-left terminal-text">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="table-row border-b border-gray-800">
                            <td class="py-3 px-4 terminal-text"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="py-3 px-4">
                                <div class="font-medium"><?php echo htmlspecialchars($user['name']); ?></div>
                                <?php if (!empty($user['user_agent'])): ?>
                                <div class="text-gray-500 text-xs mt-1">
                                    <?php echo htmlspecialchars(substr($user['user_agent'], 0, 30)) . '...'; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 terminal-text font-mono text-sm">
                                <?php echo htmlspecialchars($user['fingerprint_id']); ?>
                            </td>
                            <td class="py-3 px-4 terminal-text">
                                <?php echo htmlspecialchars($user['ip_address']); ?>
                            </td>
                            <td class="py-3 px-4 terminal-text">
                                <?php echo date('Y-m-d H:i:s', strtotime($user['submission_time'])); ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="status-badge status-active">ACTIVE</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Table Info -->
            <div class="mt-6 flex justify-between items-center terminal-text text-sm text-gray-400">
                <div>
                    Showing <?php echo $totalUsers; ?> record<?php echo $totalUsers !== 1 ? 's' : ''; ?>
                </div>
                <div>
                    <a href="index.html" class="text-blue-400 hover:text-blue-300">
                        <i class="fas fa-plus mr-1"></i> New Scan
                    </a>
                </div>
            </div>
            
            <?php else: ?>
            <div class="text-center py-10">
                <i class="fas fa-database text-5xl mb-4 text-gray-700"></i>
                <h4 class="text-xl mb-2 terminal-text">No scan data available</h4>
                <p class="text-gray-500 mb-6">No users have submitted their details yet.</p>
                <a href="index.html" class="btn-primary inline-block w-auto px-8">
                    <i class="fas fa-fingerprint mr-2"></i> Go to Scanner
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Admin Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="neon-border rounded-lg p-6 bg-black bg-opacity-80">
                <h4 class="header-font text-lg mb-4">EXPORT DATA</h4>
                <p class="text-gray-400 mb-4 terminal-text">Export scan records in various formats</p>
                <button onclick="alert('Export functionality would be implemented here.')" 
                        class="btn-primary w-full mb-3">
                    <i class="fas fa-file-csv mr-2"></i> Export as CSV
                </button>
                <button onclick="alert('Export functionality would be implemented here.')" 
                        class="btn-primary w-full" style="background-color: #28a745;">
                    <i class="fas fa-file-excel mr-2"></i> Export as Excel
                </button>
            </div>
            
            <div class="neon-border rounded-lg p-6 bg-black bg-opacity-80">
                <h4 class="header-font text-lg mb-4">SYSTEM LOGS</h4>
                <div class="terminal-text text-sm space-y-2">
                    <div class="text-green-400">
                        <i class="fas fa-circle mr-2"></i> Database connection: OK
                    </div>
                    <div class="text-green-400">
                        <i class="fas fa-circle mr-2"></i> Authentication: ACTIVE
                    </div>
                    <div class="text-green-400">
                        <i class="fas fa-circle mr-2"></i> Records loaded: <?php echo $totalUsers; ?>
                    </div>
                    <div class="text-green-400">
                        <i class="fas fa-circle mr-2"></i> Session: SECURE
                    </div>
                    <div class="text-blue-400">
                        <i class="fas fa-circle mr-2"></i> Memory usage: <?php echo round(memory_get_usage() / 1024 / 1024, 2); ?>MB
                    </div>
                </div>
            </div>
            
            <div class="neon-border rounded-lg p-6 bg-black bg-opacity-80">
                <h4 class="header-font text-lg mb-4">QUICK ACTIONS</h4>
                <div class="space-y-3">
                    <button onclick="location.reload()" class="btn-primary w-full" style="background-color: #17a2b8;">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                    </button>
                    <button onclick="alert('This would clear old records in a real application.')" 
                            class="btn-primary w-full" style="background-color: #ffc107; color: #000;">
                        <i class="fas fa-trash-alt mr-2"></i> Clear Old Records
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-10 pt-6 border-t border-gray-800 text-center text-gray-500 terminal-text text-sm">
            <p>Neural Security Interface Admin Panel v2.5 | © 2023 Secure Systems Division</p>
            <p class="mt-2">All access to this panel is logged and monitored.</p>
        </footer>
    </div>
    <?php endif; ?>
    
    <script>
        // Auto-refresh page every 30 seconds when on admin dashboard
        <?php if ($isLoggedIn): ?>
        setTimeout(function() {
            location.reload();
        }, 30000);
        <?php endif; ?>
        
        // Add confirmation for logout
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>