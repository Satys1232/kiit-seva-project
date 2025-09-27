<?php
require_once dirname(__DIR__) . '/models/BaseModel.php';
use App\Models\BaseModel;

class Feedback extends BaseModel {
    protected string $table = 'feedback';

    public function create(array $data): int|false {
        $required = ['user_id', 'category', 'subject', 'message', 'rating'];
        if (!$this->validateRequired($required, $data)) {
            return false;
        }
        return $this->insert($this->table, $data);
    }

    public function recent(int $limit = 10): array|false {
        return $this->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT {$limit}"
        );
    }
}
?>

