<?php
/**
 * PHPUnit Bootstrap file for WHMCS Automation Hub
 */

require_once __DIR__ . '/../lib/TriggerInterface.php';
require_once __DIR__ . '/../lib/ActionInterface.php';
require_once __DIR__ . '/../lib/HttpClient.php';
require_once __DIR__ . '/../lib/Registry.php';
require_once __DIR__ . '/../lib/RuleEngine.php';

// Require all triggers
foreach (glob(__DIR__ . '/../triggers/*Trigger.php') as $file) {
    require_once $file;
}

// Require all actions
foreach (glob(__DIR__ . '/../actions/*Action.php') as $file) {
    require_once $file;
}

// Mock WHMCS Capsule DB class if not present in CLI environment
if (!class_exists('WHMCS\Database\Capsule')) {
    eval('
    namespace WHMCS\Database;
    class Capsule {
        public static function table($name) {
            return new static();
        }
        public static function schema() {
            return new static();
        }
        public function where() { return $this; }
        public function whereIn() { return $this; }
        public function whereDate() { return $this; }
        public function orderBy() { return $this; }
        public function skip() { return $this; }
        public function take() { return $this; }
        public function get() { return new \ArrayObject([]); }
        public function first() { return null; }
        public function count() { return 0; }
        public function insert() { return true; }
        public function update() { return true; }
        public function delete() { return true; }
        public function truncate() { return true; }
        public function exists() { return false; }
        public function toArray() { return []; }
    }
    ');
}
