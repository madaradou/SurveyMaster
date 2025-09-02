<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front_office/login.php');
    exit();
}
require_once '../../../controllers/UserController.php';
$userController = new UserController();
$users = $userController->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>User Management - Hotelia Smart Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="template/assets/logo/HS.png" type="image/png" />
    <script src="template/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["template/assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>
    <link rel="stylesheet" href="template/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="template/assets/css/plugins.min.css" />
    <link rel="stylesheet" href="template/assets/css/hotelia smart.min.css" />
    <link rel="stylesheet" href="template/assets/css/demo.css" />
</head>
<body>
    <div class="wrapper">
        <?php include 'dashboard_side_bar.php'; ?>
        <div class="main-panel">
            <?php include 'header_bar.php'; ?>
            <div class="content">
                <div class="page-inner">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">User Management</h4>
                                    <div style="margin-top:10px; display: flex; align-items: center; gap: 15px;">
                                        <label for="sortUsers">Sort by: </label>
                                        <select id="sortUsers" class="form-control" style="width:auto;display:inline-block;">
                                            <option value="name">Name</option>
                                            <option value="role">Role</option>
                                            <option value="status">Status</option>
                                        </select>
                                        <input type="text" id="searchUsers" class="form-control" placeholder="Search by name or email" style="width:220px;display:inline-block;" />
                                        <button id="exportPdfBtn" class="btn btn-primary" style="margin-left:10px;">Export PDF</button>
                                        <button id="exportExcelBtn" class="btn btn-success" style="margin-left:5px;">Export Excel</button>
                                        <a href="admin_messages.php" class="btn btn-info" style="margin-left:10px;">Messages</a>
                                        <a href="broadcast.php" class="btn btn-warning" style="margin-left:0;">Broadcast</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Verified</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="usersTableBody">
                                                <?php foreach ($users as $user): ?>
                                                <tr data-user-id="<?php echo $user['user_id']; ?>">
                                                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                                    <td><?php echo $user['banned'] ? 'Banned' : 'Active'; ?></td>
                                                    <td><?php echo isset($user['verified']) ? ($user['verified'] ? '<span style="color:green;font-weight:bold;">Yes</span>' : '<span style="color:red;font-weight:bold;">No</span>') : '<span style="color:gray;">N/A</span>'; ?></td>
                                                    <td>
                                                        <?php if ($user['role'] === 'admin'): ?>
                                                            <!-- No actions for admin -->
                                                        <?php else: ?>
                                                            <?php if ($user['banned']): ?>
                                                                <button onclick="unbanUser(<?php echo $user['user_id']; ?>)" class="btn btn-success btn-sm btn-unban">Unban</button>
                                                            <?php else: ?>
                                                                <button onclick="banUser(<?php echo $user['user_id']; ?>)" class="btn btn-danger btn-sm btn-ban">Ban</button>
                                                            <?php endif; ?>

                                                            <?php if ($user['role'] === 'client'): ?>
                                                                <button onclick="setRole(<?php echo $user['user_id']; ?>, 'agent')" class="btn btn-warning btn-sm btn-role">Make Agent</button>
                                                            <?php elseif ($user['role'] === 'agent'): ?>
                                                                <button onclick="setRole(<?php echo $user['user_id']; ?>, 'client')" class="btn btn-info btn-sm btn-role">Make Client</button>
                                                            <?php endif; ?>

                                                            <button onclick="deleteUser(<?php echo $user['user_id']; ?>)" class="btn btn-outline-danger btn-sm">Delete</button>
                                                            <!-- Timeout/Reactivate Button and Combobox -->
                                                            <select class="timeout-duration" style="width:auto;display:inline-block;">
                                                                <option value="30">30s</option>
                                                                <option value="60">1 min</option>
                                                                <option value="120">2 min</option>
                                                                <option value="300">5 min</option>
                                                                <option value="600">10 min</option>
                                                                <option value="1800">30 min</option>
                                                                <option value="86400">1 day</option>
                                                                <option value="2592000">1 month</option>
                                                            </select>
                                                            <button onclick="timeoutUser(<?php echo $user['user_id']; ?>, this)" class="btn btn-secondary btn-sm btn-timeout">Timeout</button>
                                                            <button onclick="reactivateUser(<?php echo $user['user_id']; ?>)" class="btn btn-success btn-sm btn-reactivate" style="display:none;">Reactivate</button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="template/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="template/assets/js/core/popper.min.js"></script>
    <script src="template/assets/js/core/bootstrap.min.js"></script>
    <script src="template/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="template/assets/js/plugin/chart.js/chart.min.js"></script>
    <script src="template/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script src="template/assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="template/assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="template/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="template/assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="template/assets/js/plugin/jsvectormap/world.js"></script>
    <script src="template/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="template/assets/js/kaiadmin.min.js"></script>
    <script src="template/assets/js/setting-demo.js"></script>
    <script src="template/assets/js/demo.js"></script>
    <script>
    $(document).ready(function() {
        function getCellValue(row, idx) {
            return $(row).children('td').eq(idx).text().toLowerCase();
        }
        function sortTableBy(col, isStatus) {
            var rows = $('#usersTableBody tr').get();
            rows.sort(function(a, b) {
                var A = getCellValue(a, col);
                var B = getCellValue(b, col);
                if(isStatus) {
                    // Active before Banned
                    if(A === B) return 0;
                    if(A === 'active') return -1;
                    return 1;
                }
                if(A < B) return -1;
                if(A > B) return 1;
                return 0;
            });
            $.each(rows, function(index, row) {
                $('#usersTableBody').append(row);
            });
        }
        function filterTable() {
            var query = $('#searchUsers').val().toLowerCase();
            $('#usersTableBody tr').each(function() {
                var name = $(this).children('td').eq(1).text().toLowerCase();
                var email = $(this).children('td').eq(2).text().toLowerCase();
                if(name.indexOf(query) > -1 || email.indexOf(query) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        $('#sortUsers').on('change', function() {
            var val = $(this).val();
            if(val === 'name') sortTableBy(1, false);
            else if(val === 'role') sortTableBy(3, false);
            else if(val === 'status') sortTableBy(4, true);
        });
        $('#searchUsers').on('input', function() {
            filterTable();
        });
        // Export PDF button logic
        $('#exportPdfBtn').on('click', function() {
            // Hide action buttons for print
            var actionCells = $('#usersTableBody td:last-child, #usersTableBody th:last-child');
            actionCells.hide();
            // Print only the table
            var printContents = '<style>@media print {body * {visibility:hidden;} #usersTableBody, #usersTableBody * {visibility:visible;} #usersTableBody {position:absolute;left:0;top:0;width:100%;} table {width:100%;border-collapse:collapse;} th,td {border:1px solid #ddd;padding:8px;text-align:left;} tr:nth-child(even){background:#f2f2f2;} th {background:#f8f9fa;}}</style>';
            printContents += '<table>' + $('table').html() + '</table>';
            var win = window.open('', '', 'height=700,width=900');
            win.document.write('<html><head><title>User List PDF</title>');
            win.document.write(printContents);
            win.document.write('</head><body></body></html>');
            win.document.close();
            win.focus();
            setTimeout(function(){
                win.print();
                win.close();
                actionCells.show();
            }, 500);
        });
        // Export Excel button logic
        $('#exportExcelBtn').on('click', function() {
            var table = document.querySelector('table');
            var rows = Array.from(table.querySelectorAll('tr'));
            var csv = [];
            rows.forEach(function(row) {
                // Only include visible rows (for filtering)
                if($(row).is(':visible')) {
                    var cols = Array.from(row.querySelectorAll('th,td'));
                    var rowData = cols.slice(0, 6).map(function(cell) {
                        // Remove commas and newlines for CSV
                        return '"' + cell.innerText.replace(/\n/g, ' ').replace(/"/g, '""').replace(/,/g, ' ') + '"';
                    });
                    csv.push(rowData.join(','));
                }
            });
            var csvString = csv.join('\n');
            var blob = new Blob([csvString], { type: 'text/csv' });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'users_list.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
    </script>
    <script src="users_management.js"></script>
</body>
</html>

<script>
// Timeout/Reactivate logic
function timeoutUser(userId, selectElem) {
    var duration = $(selectElem).siblings('.timeout-duration').val();
    var row = $(selectElem).closest('tr');
    var countdownSpan = row.find('.timeout-countdown');
    if (countdownSpan.length === 0) {
        $(selectElem).after('<span class="timeout-countdown" style="margin-left:8px;font-weight:bold;color:#d9534f;"></span><button class="btn btn-outline-secondary btn-sm btn-stop-timeout" style="margin-left:5px;">Stop</button>');
        countdownSpan = row.find('.timeout-countdown');
    }
    var stopBtn = row.find('.btn-stop-timeout');
    stopBtn.show();
    var remaining = parseInt(duration);
    countdownSpan.text(formatDuration(remaining));
    clearInterval(countdownSpan.data('timer'));
    var timer = setInterval(function() {
        remaining--;
        if (remaining > 0) {
            countdownSpan.text(formatDuration(remaining));
        } else {
            countdownSpan.text('');
            clearInterval(timer);
            stopBtn.hide();
        }
    }, 1000);
    countdownSpan.data('timer', timer);
    stopBtn.off('click').on('click', function() {
        clearInterval(timer);
        countdownSpan.text('');
        stopBtn.hide();
        // Optionally notify backend to cancel timeout
        fetch('timeout_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'stop_timeout', user_id: userId })
        });
    });
    fetch('timeout_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'timeout', user_id: userId, duration: duration })
    })
    .then(response => response.json())
    .then(response => {
        if (response.status) {
            alert('User timed out for ' + duration + ' seconds.');
        } else {
            alert('Failed to timeout user: ' + response.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

function reactivateUser(userId) {
    var row = $("tr[data-user-id='" + userId + "']");
    var countdownSpan = row.find('.timeout-countdown');
    var stopBtn = row.find('.btn-stop-timeout');
    if (countdownSpan.length) {
        clearInterval(countdownSpan.data('timer'));
        countdownSpan.text('');
    }
    if (stopBtn.length) {
        stopBtn.hide();
    }
    fetch('timeout_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'reactivate', user_id: userId })
    })
    .then(response => response.json())
    .then(response => {
        if (response.status) {
            alert('User reactivated successfully.');
        } else {
            alert('Failed to reactivate user: ' + response.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

function formatDuration(seconds) {
    if (seconds < 60) return seconds + 's';
    if (seconds < 3600) return Math.floor(seconds/60) + 'm ' + (seconds%60) + 's';
    if (seconds < 86400) return Math.floor(seconds/3600) + 'h ' + Math.floor((seconds%3600)/60) + 'm';
    return Math.floor(seconds/86400) + 'd ' + Math.floor((seconds%86400)/3600) + 'h';
}
</script>