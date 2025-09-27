<?php
require_once dirname(__DIR__) . '/models/BaseModel.php';
use App\Models\BaseModel;

class Teacher extends BaseModel {
    protected string $table = 'teachers';

    public function getActiveTeachersWithUser(): array|false {
        $sql = "SELECT u.id as user_id, u.name, u.email, t.department, t.chamber_no, t.available_slots, t.is_active
                FROM users u INNER JOIN teachers t ON u.id = t.user_id
                WHERE u.is_active = 1 AND t.is_active = 1
                ORDER BY u.name ASC";
        return $this->fetchAll($sql, []);
    }

    public function getTeacherSlots(int $userId): array {
        $row = $this->fetchOne("SELECT available_slots FROM {$this->table} WHERE user_id = ? AND is_active = 1", [$userId]);
        if (!$row || empty($row['available_slots'])) {
            return [];
        }
        $json = json_decode($row['available_slots'], true);
        return is_array($json) ? $json : [];
    }
}
?>

