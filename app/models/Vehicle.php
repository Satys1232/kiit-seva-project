<?php
require_once dirname(__DIR__) . '/models/BaseModel.php';
use App\Models\BaseModel;

class Vehicle extends BaseModel {
    protected string $table = 'vehicles';

    public function getActiveVehicles(?string $route = null): array|false {
        $sql = "SELECT id, vehicle_number, route, driver_name, driver_phone, capacity, current_load, current_lat, current_lng, duty_status, last_updated, is_active
                FROM {$this->table}
                WHERE is_active = 1";
        $params = [];
        if ($route) {
            $sql .= " AND route = ?";
            $params[] = $route;
        }
        $sql .= " ORDER BY route, vehicle_number";
        return $this->fetchAll($sql, $params);
    }
}
?>

