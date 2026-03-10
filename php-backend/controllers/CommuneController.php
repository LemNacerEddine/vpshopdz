<?php
/**
 * Commune Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../data/communes.php';
require_once __DIR__ . '/../utils/helpers.php';

class CommuneController {

    public function __construct() {
        // No DB needed - uses static data
    }

    // GET /communes?wilaya=16 or /communes?wilaya_name=16 - الجزائر (Alger)
    public function index() {
        $wilayaCode = $_GET['wilaya'] ?? null;
        $wilayaName = $_GET['wilaya_name'] ?? null;

        if ($wilayaCode) {
            $communes = getCommunesByWilaya((int)$wilayaCode);
        } elseif ($wilayaName) {
            $communes = getCommunesByWilayaName($wilayaName);
        } else {
            errorResponse('يرجى تحديد الولاية', 400);
        }

        jsonResponse($communes);
    }
}
