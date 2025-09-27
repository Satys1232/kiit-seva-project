<?php
require_once dirname(__DIR__) . '/models/BaseModel.php';

class Booking extends BaseModel {
    protected string $table = 'bookings';
    
    public function createBooking(array $data): int|false {
        if ($this->checkSlotConflict($data['teacher_id'], $data['booking_date'], $data['time_slot'])) {
            return false;
        }
        return $this->insert($this->table, $data);
    }
    
    public function getBookingsByStudent(int $studentId): array|false {
        return $this->fetchAll(
            "SELECT b.*, u.name as teacher_name FROM {$this->table} b 
             JOIN users u ON b.teacher_id = u.id 
             WHERE b.student_id = ? ORDER BY b.booking_date DESC",
            [$studentId]
        );
    }
    
    public function getBookingsByTeacher(int $teacherId): array|false {
        return $this->fetchAll(
            "SELECT b.*, u.name as student_name FROM {$this->table} b 
             JOIN users u ON b.student_id = u.id 
             WHERE b.teacher_id = ? ORDER BY b.booking_date DESC",
            [$teacherId]
        );
    }
    
    private function checkSlotConflict(int $teacherId, string $date, string $timeSlot): bool {
        return $this->exists($this->table, [
            'teacher_id' => $teacherId,
            'booking_date' => $date,
            'time_slot' => $timeSlot
        ]);
    }
}
?>