<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

/*
  supply (production) — aggregated from batch_quantity.production_date & batch_quantity.batch_yield_kg grouped by region
  demand (consumption) — aggregated from meat_consumption_by_region.consumption grouped by region
  both tables exist in your schema
*/

$sql = "
  SELECT
    COALESCE(p.region, c.region_name, 'Unknown') AS region,
    COALESCE(p.prod_kg,0) AS production,
    COALESCE(c.consumption,0) AS consumption
  FROM
    ( SELECT region, SUM(batch_yield_kg) AS prod_kg FROM batch_quantity GROUP BY region ) p
  FULL JOIN
    ( SELECT slaughterhouse_ID, region_name, SUM(consumption) AS consumption FROM meat_consumption_by_region GROUP BY region_name ) c
  ON p.region = c.region_name
";

/*
 Note: MySQL (older versions) doesn't support FULL JOIN. Use UNION approach to ensure compatibility.
 We'll instead build via union to produce region-level production and consumption.
*/
$sql = "
  SELECT region, SUM(prod_kg) AS production, 0 AS consumption FROM (
    SELECT region, batch_yield_kg AS prod_kg FROM batch_quantity
  ) t GROUP BY region
  UNION
  SELECT region_name AS region, 0 AS production, SUM(consumption) AS consumption FROM meat_consumption_by_region GROUP BY region_name
";

$res = $conn->query($sql);
$map = [];
while ($r = $res->fetch_assoc()){
  $rg = $r['region'] ?: 'Unknown';
  if (!isset($map[$rg])) $map[$rg] = ['region'=>$rg,'production'=>0,'consumption'=>0];
  $map[$rg]['production'] += (float)$r['production'];
  $map[$rg]['consumption'] += (float)$r['consumption'];
}
$out = array_values($map);
echo json_encode($out);
$conn->close();
