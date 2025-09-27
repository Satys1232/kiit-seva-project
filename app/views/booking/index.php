<?php
session_start();
require_once dirname(__DIR__, 2) . '/helpers/functions.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    redirect('/login');
}
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Teacher - KIIT SEVA</title>
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
        <h1>Book Teacher Appointment</h1>
        <?php
            require_once dirname(__DIR__, 2) . '/models/Teacher.php';
            $teacherModel = new Teacher();
            $teachers = $teacherModel->getActiveTeachersWithUser() ?: [];
        ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-top: 15px;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <?php foreach ($teachers as $t): ?>
                <div class="col-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div style="font-size: 3rem; margin-bottom: 20px;">👨🏫</div>
                            <h3><?php echo htmlspecialchars($t['name']); ?></h3>
                            <p class="text-muted"><?php echo htmlspecialchars($t['department']); ?></p>
                            <p><strong>Chamber:</strong> <?php echo htmlspecialchars($t['chamber_no'] ?? ''); ?></p>
                            <button class="btn btn-primary" onclick="bookTeacher(<?php echo (int)$t['user_id']; ?>)">Book Appointment</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($teachers)): ?>
                <div class="col-12"><p>No active teachers found.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 15px; width: 90%; max-width: 500px;">
            <h3>Book Appointment</h3>
            <form id="bookingForm" method="POST" action="/booking/create">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="booking_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Time Slot</label>
                    <select name="time_slot" id="time_slot" class="form-control" required>
                        <option value="">Select time...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Purpose</label>
                    <textarea name="purpose" class="form-control" rows="3" required placeholder="Reason for meeting..."></textarea>
                </div>
                <input type="hidden" name="teacher_id" id="teacher_id_hidden" value="">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Book Appointment</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedTeacher = null;
        const teacherSlots = <?php
            // Build a map of teacher_id => slots JSON grouped by weekday
            $slotMap = [];
            foreach ($teachers as $t) {
                $slots = $teacherModel->getTeacherSlots((int)$t['user_id']);
                $slotMap[(int)$t['user_id']] = $slots;
            }
            echo json_encode($slotMap);
        ?>;

        function bookTeacher(teacherId) {
            selectedTeacher = teacherId;
            document.getElementById('bookingModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (!selectedTeacher) {
                e.preventDefault();
                alert('Please select a teacher.');
                return;
            }
            document.getElementById('teacher_id_hidden').value = selectedTeacher;
        });

        // Populate time slots when date changes
        document.querySelector('input[name="booking_date"]').addEventListener('change', function() {
            const date = new Date(this.value);
            if (!selectedTeacher || isNaN(date.getTime())) return;
            const weekday = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'][date.getDay()];
            const slotsForTeacher = teacherSlots[selectedTeacher] || {};
            const slots = slotsForTeacher[weekday] || [];
            const select = document.getElementById('time_slot');
            select.innerHTML = '<option value="">Select time...</option>';
            slots.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s;
                opt.textContent = s;
                select.appendChild(opt);
            });
        });
    </script>
</body>
</html>