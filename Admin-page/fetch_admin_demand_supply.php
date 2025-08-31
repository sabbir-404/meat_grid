<?php
// Admin-page/fetch_admin_demand_supply.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Project-root/db_connect.php';

try {
    // Accept optional filters
    $sku        = isset($_GET['sku']) && $_GET['sku'] !== '' ? $conn->real_escape_string($_GET['sku']) : null;
    $product_id = isset($_GET['product_id']) && $_GET['product_id'] !== '' ? $conn->real_escape_string($_GET['product_id']) : null;
    $region     = isset($_GET['region']) && $_GET['region'] !== '' ? $conn->real_escape_string($_GET['region']) : null;
    $date_from  = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $conn->real_escape_string($_GET['date_from']) : null;
    $date_to    = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $conn->real_escape_string($_GET['date_to']) : null;

    function table_exists($conn, $table) {
        $t = $conn->real_escape_string($table);
        $res = $conn->query("SHOW TABLES LIKE '$t'");
        return ($res && $res->num_rows > 0);
    }

    $out = [
        'product_details' => [],
        'price_comparison' => [],
        'price_history' => [],
        'production_rate_timeseries' => [],
        'production_by_region' => [],
        'nutritional_intake_timeseries' => [],
        'percapita_consumption' => [],
        'supply_summary' => [],
        'demand_supply_comparison' => [],
        'alt_protein' => [],
        'elasticity' => []
    ];

    // ---------- product_details (for dropdown)
    if (table_exists($conn, 'processed_product')) {
        $sql = "SELECT sku, name, category, price_per_kg FROM processed_product WHERE 1=1";
        if ($sku) $sql .= " AND sku = '" . $conn->real_escape_string($sku) . "'";
        $sql .= " ORDER BY name ASC";
        $res = $conn->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) $out['product_details'][] = $r;
        }
    }

    // ---------- price_history and price_comparison
    if (table_exists($conn, 'processed_price_history')) {
        $where = " WHERE 1=1 ";
        if ($sku) $where .= " AND sku = '" . $conn->real_escape_string($sku) . "'";
        if ($region) $where .= " AND region = '" . $conn->real_escape_string($region) . "'";
        if ($date_from) $where .= " AND date_recorded >= '" . $conn->real_escape_string($date_from) . "'";
        if ($date_to) $where .= " AND date_recorded <= '" . $conn->real_escape_string($date_to) . "'";

        $sql = "SELECT sku, region, date_recorded, wholesale_price, retail_price, source, notes
                FROM processed_price_history
                $where
                ORDER BY sku, date_recorded ASC";
        $res = $conn->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) $out['price_history'][] = $r;
        }

        // build price_comparison = latest retail per sku (simple)
        $sql2 = "SELECT p.sku, p.region, p.date_recorded, p.retail_price
                 FROM processed_price_history p
                 JOIN (
                    SELECT sku, MAX(date_recorded) AS mx FROM processed_price_history GROUP BY sku
                 ) latest ON latest.sku = p.sku AND latest.mx = p.date_recorded
                 ORDER BY p.sku";
        $res2 = $conn->query($sql2);
        if ($res2) {
            while ($r = $res2->fetch_assoc()) $out['price_comparison'][] = $r;
        }
    }

    // ---------- production_rate_timeseries (processed_batch + batch_quantity)
    $timeSeries = [];
    if (table_exists($conn, 'processed_batch')) {
        $where = " WHERE 1=1 ";
        if ($region) $where .= " AND region = '" . $conn->real_escape_string($region) . "' ";
        if ($date_from) $where .= " AND processing_date >= '" . $conn->real_escape_string($date_from) . "' ";
        if ($date_to) $where .= " AND processing_date <= '" . $conn->real_escape_string($date_to) . "' ";

        $sql = "SELECT DATE_FORMAT(processing_date, '%Y-%m') AS ym,
                       ROUND(SUM(COALESCE(batch_yield_kg,0))/1000,3) AS tons
                FROM processed_batch
                $where
                GROUP BY ym
                ORDER BY ym ASC";
        $res = $conn->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $timeSeries[$r['ym']] = ($timeSeries[$r['ym']] ?? 0) + floatval($r['tons']);
            }
        }

        // production_by_region (aggregate)
        $sql2 = "SELECT region, DATE_FORMAT(processing_date,'%Y-%m') as ym, ROUND(SUM(COALESCE(batch_yield_kg,0))/1000,3) AS tons
                 FROM processed_batch
                 GROUP BY region, ym
                 ORDER BY region, ym";
        $res2 = $conn->query($sql2);
        if ($res2) {
            while ($r = $res2->fetch_assoc()) $out['production_by_region'][] = $r;
        }
    }
    if (table_exists($conn, 'batch_quantity')) {
        $where = " WHERE 1=1 ";
        if ($region) $where .= " AND region = '" . $conn->real_escape_string($region) . "' ";
        if ($date_from) $where .= " AND production_date >= '" . $conn->real_escape_string($date_from) . "' ";
        if ($date_to) $where .= " AND production_date <= '" . $conn->real_escape_string($date_to) . "' ";

        $sql = "SELECT DATE_FORMAT(production_date, '%Y-%m') AS ym,
                       ROUND(SUM(COALESCE(batch_yield_kg,0))/1000,3) AS tons
                FROM batch_quantity
                $where
                GROUP BY ym
                ORDER BY ym ASC";
        $res = $conn->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $timeSeries[$r['ym']] = ($timeSeries[$r['ym']] ?? 0) + floatval($r['tons']);
            }
        }
    }
    ksort($timeSeries);
    foreach ($timeSeries as $ym => $tons) $out['production_rate_timeseries'][] = ['period' => $ym, 'tons' => $tons];

    // ---------- nutritional_intake_timeseries
    if (table_exists($conn, 'nutrient_intake_over_time')) {
        $sql = "SELECT month, ROUND(AVG(COALESCE(protein,0)),2) AS protein, ROUND(AVG(COALESCE(iron,0)),2) AS iron, ROUND(AVG(COALESCE(vitamin,0)),2) AS vitamin
                FROM nutrient_intake_over_time
                GROUP BY month
                ORDER BY month ASC";
        $res = $conn->query($sql);
        if ($res) while ($r = $res->fetch_assoc()) $out['nutritional_intake_timeseries'][] = $r;
    }

    // ---------- percapita_consumption
    if (table_exists($conn, 'meat_consumption_by_region')) {
        $where = " WHERE 1=1 ";
        if ($region) $where .= " AND region_name = '" . $conn->real_escape_string($region) . "' ";
        $sql = "SELECT region_name, ROUND(AVG(COALESCE(consumption,0)),3) AS percapita_kg
                FROM meat_consumption_by_region
                $where
                GROUP BY region_name
                ORDER BY region_name ASC";
        $res = $conn->query($sql);
        if ($res) while ($r = $res->fetch_assoc()) $out['percapita_consumption'][] = $r;
    }

    // ---------- supply_summary
    if (table_exists($conn, 'policy_supply')) {
        $where = " WHERE 1=1 ";
        if ($region) $where .= " AND division = '" . $conn->real_escape_string($region) . "' ";
        $sql = "SELECT division, livestock_count, slaughter_rate, total_yield, record_date FROM policy_supply $where ORDER BY record_date DESC LIMIT 200";
        $res = $conn->query($sql);
        if ($res) while ($r = $res->fetch_assoc()) $out['supply_summary'][] = $r;
    }

    // ---------- alt_protein
    if (table_exists($conn, 'policy_alt_protein')) {
        $sql = "SELECT id, category, avg_price, avg_qty, record_date FROM policy_alt_protein ORDER BY record_date DESC LIMIT 200";
        $res = $conn->query($sql);
        if ($res) while ($r = $res->fetch_assoc()) $out['alt_protein'][] = $r;
    }

    // ---------- elasticity
    if (table_exists($conn, 'processed_elasticity')) {
        $where = " WHERE 1=1 ";
        if ($sku) $where .= " AND sku = '" . $conn->real_escape_string($sku) . "' ";
        if ($region) $where .= " AND region = '" . $conn->real_escape_string($region) . "' ";
        $sql = "SELECT id, sku, region, timeframe_start, timeframe_end, own_price_elasticity, cross_price_elasticity_plant, notes, computed_at
                FROM processed_elasticity
                $where
                ORDER BY computed_at DESC
                LIMIT 200";
        $res = $conn->query($sql);
        if ($res) while ($r = $res->fetch_assoc()) $out['elasticity'][] = $r;
    }

    // ---------- demand_supply_comparison (basic join of supply_summary with percapita if available)
    // We attempt a simple comparison: for divisions in policy_supply, find percapita (if present) and estimate demand (region pop estimate not available).
    if (!empty($out['supply_summary'])) {
        foreach ($out['supply_summary'] as $s) {
            $entry = [
                'division' => $s['division'],
                'total_supply_tons' => floatval($s['total_yield']),
                'record_date' => $s['record_date'],
                'region' => $s['division']
            ];
            // attempt estimated demand from percapita_consumption table (if same region name exists)
            foreach ($out['percapita_consumption'] as $pc) {
                if (strcasecmp(trim($pc['region_name'] ?? $pc['region']), trim($s['division'])) === 0 || strcasecmp(trim($pc['region'] ?? ''), trim($s['division'])) === 0) {
                    // We don't have population numbers — put the percapita as proxy (kg/person)
                    $entry['estimated_percapita_kg'] = floatval($pc['percapita_kg'] ?? $pc['percapita'] ?? $pc['consumption'] ?? 0);
                }
            }
            $out['demand_supply_comparison'][] = $entry;
        }
    }

    echo json_encode($out);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()]);
}
