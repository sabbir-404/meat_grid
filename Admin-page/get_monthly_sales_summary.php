<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../Project-root/db_connect.php";

// Build last 6 months labels
$labels = [];
for ($i = 5; $i >= 0; $i--) {
  $labels[] = date("Y-m", strtotime("-$i months"));
}

// Query sums grouped by month and retailer
$sql = "
  SELECT DATE_FORMAT(p.purchase_date, '%Y-%m') as ym, p.buyer_ID as retailer_ID, SUM(p.total_price) as total
  FROM purchase p
  WHERE p.buyer_type = 'retailer' AND p.purchase_date IS NOT NULL
    AND p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY ym, p.buyer_ID
  ORDER BY ym ASC;
";
$res = $conn->query($sql);
$map = []; // map[retailer_ID][ym] = total
$retailers = [];
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $ym = $r['ym'];
    $rid = $r['retailer_ID'];
    $total = (float)$r['total'];
    $map[$rid][$ym] = $total;
    if (!in_array($rid, $retailers)) $retailers[] = $rid;
  }
}

// build datasets for chart.js
$datasets = [];
$colors = ['#007bff','#28a745','#dc3545','#ffc107','#6610f2','#20c997','#fd7e14'];
$ci = 0;
foreach ($retailers as $rid) {
  $data = [];
  foreach ($labels as $lab) $data[] = isset($map[$rid][$lab]) ? (float)$map[$rid][$lab] : 0.0;
  $datasets[] = [
    'label' => 'Retailer ' . $rid,
    'data' => $data,
    'backgroundColor' => $colors[$ci % count($colors)]
  ];
  $ci++;
}

echo json_encode(['labels'=>$labels,'datasets'=>$datasets]);
$conn->close();

