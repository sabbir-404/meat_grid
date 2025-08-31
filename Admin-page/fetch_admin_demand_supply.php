<?php
// fetch_admin_demand_supply.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Project-root/db_connect.php'; // matches your project layout

try {
    // Accept optional filters
    $sku        = isset($_GET['sku']) && $_GET['sku'] !== '' ? $conn->real_escape_string($_GET['sku']) : null;
    $product_id = isset($_GET['product_id']) && $_GET['product_id'] !== '' ? $conn->real_escape_string($_GET['product_id']) : null;
    $region     = isset($_GET['region']) && $_GET['region'] !== '' ? $conn->real_escape_string($_GET['region']) : null;
    $date_from  = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $conn->real_escape_string($_GET['date_from']) : null;
    $date_to    = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $conn->real_escape_string($_GET['date_to']) : null;

    // helper to check tables (defensive)
    function table_exists($conn, $table) {
        $t = $conn->real_escape_string($table);
        $res = $conn->query("SHOW TABLES LIKE '$t'");
        return ($res && $res->num_rows > 0);
    }

    $out = [
        'price_comparison' => [],             // latest price per product/sku
        'production_rate_timeseries' => [],   // month -> tons
        'nutritional_intake_timeseries' => [],// month -> nutrient aggregates
        'percapita_consumption' => [],        // region -> percapita kg
        'supply_summary' => [],               // policy_supply
        'alt_protein' => [],                  // policy_alt_protein
        'elasticity' => []                    // processed_elasticity
    ];

    // --- PRICE COMPARISON ---
    // Prefer processed_product.price_per_kg, otherwise latest processed_price_history retail price
    if (table_exists($conn, 'processed_product')) {
        $sql = "SELECT sku, name, COALESCE(price_per_kg, 0) AS price_per_kg FROM processed_product WHERE 1=1";
        if ($sku) $sql .= " AND sku = '" . $conn->real_escape_string($sku) . "'";
        $res = $conn->query($sql);
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) {
                $out['price_comparison'][] = [
                    'sku' => $r['sku'],
                    'name' => $r['name'],
                    'price_per_kg' => $r['price_per_kg'] !== null ? floatval($r['price_per_kg']) : null
                ];
            }
        }
    }
    // supplement with processed_price_history latest retail price where processed_product missing price
    if (table_exists($conn, 'processed_price_history')) {
        // get latest price per sku
        $where = " WHERE 1=1 ";
        if ($sku) $where .= " AND sku = '" . $conn->real_escape_string($sku) . "'";
        if ($region) $where .= " AND region = '" . $conn->real_escape_string($region) . "'";
        if ($date_from) $where .= " AND date_recorded >= '" . $conn->real_escape_string($date_from) . "'";
        if ($date_to) $where .= " AND date_recorded <= '" . $conn->real_escape_string($date_to) . "'";

        $sql = "SELECT p.sku, p.region, p.date_recorded, p.retail_price
                FROM processed_price_history p
                $where
                ORDER BY p.sku, p.date_recorded DESC";
        $res = $conn->query($sql);
        if ($res !== false) {
            // We'll keep latest seen per sku if not already present
            $seen = [];
            while ($r = $res->fetch_assoc()) {
                $s = $r['sku'];
                if (isset($seen[$s])) continue;
                $seen[$s] = true;
                $out['price_comparison'][] = [
                    'sku' => $s,
                    'region' => $r['region'],
                    'date' => $r['date_recorded'],
                    'retail_price' => $r['retail_price'] !== null ? floatval($r['retail_price']) : null
                ];
            }
        }
    }

    // --- PRODUCTION RATE TIMESERIES ---
    // Use processed_batch.batch_yield_kg (processing_date) and batch_quantity.batch_yield_kg (production_date)
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
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) {
                $timeSeries[$r['ym']] = ($timeSeries[$r['ym']] ?? 0) + floatval($r['tons']);
            }
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
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) {
                $timeSeries[$r['ym']] = ($timeSeries[$r['ym']] ?? 0) + floatval($r['tons']);
            }
        }
    }
    // convert to array sorted by month
    ksort($timeSeries);
    foreach ($timeSeries as $ym => $tons) {
        $out['production_rate_timeseries'][] = ['period' => $ym, 'tons' => $tons];
    }

    // --- NUTRITIONAL INTAKE TIMESERIES ---
    if (table_exists($conn, 'nutrient_intake_over_time')) {
        $where = " WHERE 1=1 ";
        if ($date_from) { /* this table stores month strings; optionally parse */ }
        $sql = "SELECT month, ROUND(AVG(COALESCE(protein,0)),2) AS protein, ROUND(AVG(COALESCE(iron,0)),2) AS iron, ROUND(AVG(COALESCE(vitamin,0)),2) AS vitamin
                FROM nutrient_intake_over_time
                GROUP BY month
                ORDER BY month ASC";
        $res = $conn->query($sql);
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) {
                $out['nutritional_intake_timeseries'][] = $r;
            }
        }
    }

    // --- PER-CAPITA CONSUMPTION BY REGION ---
    if (table_exists($conn, 'meat_consumption_by_region')) {
        $where = " WHERE 1=1 ";
        if ($region) $where .= " AND region_name = '" . $conn->real_escape_string($region) . "' ";
        $sql = "SELECT region_name, ROUND(AVG(COALESCE(consumption,0)),3) AS percapita_kg
                FROM meat_consumption_by_region
                $where
                GROUP BY region_name
                ORDER BY region_name ASC";
        $res = $conn->query($sql);
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) {
                $out['percapita_consumption'][] = $r;
            }
        }
    }

    // --- SUPPLY SUMMARY (policy_supply) ---
    if (table_exists($conn, 'policy_supply')) {
        $where = " WHERE 1=1 ";
        if ($region) $where .= " AND division = '" . $conn->real_escape_string($region) . "' ";
        $sql = "SELECT division, livestock_count, slaughter_rate, total_yield, record_date FROM policy_supply $where ORDER BY record_date DESC LIMIT 100";
        $res = $conn->query($sql);
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) $out['supply_summary'][] = $r;
        }
    }

    // --- ALTERNATIVE PROTEIN (policy_alt_protein) ---
    if (table_exists($conn, 'policy_alt_protein')) {
        $sql = "SELECT id, category, avg_price, avg_qty, record_date FROM policy_alt_protein ORDER BY record_date DESC LIMIT 100";
        $res = $conn->query($sql);
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) $out['alt_protein'][] = $r;
        }
    }

    // --- ELASTICITY (processed_elasticity) ---
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
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) $out['elasticity'][] = $r;
        }
    }

    echo json_encode($out);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()]);
}
