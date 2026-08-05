-- ============================================================================
-- STOCK INCREMENT FORENSIC AUDIT (read-only)
-- Finds products whose quantity was INCREMENTED in connection with orders,
-- instead of being deducted — including products that were at 0 stock when
-- they were added to an order.
--
-- Context: the product "stock history" page is built from activity_log rows
-- (one row per products.quantity change). Legitimate increment writers are:
--   * stock_movements: restored / transferred_in  (leave a movement row)
--   * ProductRefill                                (activity row only, quantity-only change)
--   * Purchase approval (DB::raw)                  (NO activity row at all -> shows as a "gap")
--   * Manual product edit form                     (activity row changing quantity + other fields)
-- Anything else that increments — especially in the same second an order was
-- created/validated, with increment size == the order line quantity — is the bug.
--
-- Run: ./vendor/bin/sail exec -T mysql mysql -u<user> -p<pass> <database> < stock-increment-audit.sql
-- ============================================================================

-- ----------------------------------------------------------------------------
-- [1] ALL QUANTITY INCREMENTS from the activity log, with classification hints
--     matched_movements: stock_movements rows within 3s (legit if restored/
--     transferred_in). changed_attrs: if more than quantity -> manual edit.
-- ----------------------------------------------------------------------------
SELECT '=== [1] ALL QUANTITY INCREMENTS (activity_log) ===' AS section;
SELECT
    a.id                                            AS activity_id,
    a.subject_id                                    AS product_id,
    p.name                                          AS product,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.old.quantity')) AS SIGNED)        AS old_qty,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.attributes.quantity')) AS SIGNED) AS new_qty,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.attributes.quantity')) AS SIGNED)
      - CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.old.quantity')) AS SIGNED)    AS increment,
    a.created_at,
    u.name                                          AS causer,
    (SELECT GROUP_CONCAT(CONCAT(sm.movement_type, ':order=', IFNULL(sm.order_id, '-')) SEPARATOR ' | ')
       FROM stock_movements sm
      WHERE sm.product_id = a.subject_id
        AND ABS(TIMESTAMPDIFF(SECOND, sm.created_at, a.created_at)) <= 3) AS matched_movements,
    JSON_KEYS(JSON_EXTRACT(a.properties, '$.attributes'))                 AS changed_attrs
FROM activity_log a
JOIN products p   ON p.id = a.subject_id
LEFT JOIN users u ON u.id = a.causer_id
WHERE a.subject_type LIKE '%Product'
  AND a.event = 'updated'
  AND JSON_EXTRACT(a.properties, '$.attributes.quantity') IS NOT NULL
  AND JSON_EXTRACT(a.properties, '$.old.quantity') IS NOT NULL
  AND CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.attributes.quantity')) AS SIGNED)
    > CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.old.quantity')) AS SIGNED)
ORDER BY a.created_at, a.id;

-- ----------------------------------------------------------------------------
-- [2] SUSPECT INCREMENTS: increment happened within 10s of an order (containing
--     that product) being created or updated, and there is NO legitimate
--     restore/transfer movement in the same window. increment == line quantity
--     is the strongest signal that the order was applied backwards.
-- ----------------------------------------------------------------------------
SELECT '=== [2] SUSPECT INCREMENTS CORRELATED WITH ORDERS ===' AS section;
SELECT
    a.id            AS activity_id,
    a.subject_id    AS product_id,
    p.name          AS product,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.old.quantity')) AS SIGNED)        AS old_qty,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.attributes.quantity')) AS SIGNED) AS new_qty,
    a.created_at    AS increment_at,
    o.id            AS order_id,
    o.invoice_no,
    o.order_status,
    o.stock_affected,
    od.quantity     AS order_line_qty,
    o.created_at    AS order_created_at,
    o.updated_at    AS order_updated_at,
    CASE WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.attributes.quantity')) AS SIGNED)
            - CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.old.quantity')) AS SIGNED)
            = od.quantity
         THEN 'INCREMENT == LINE QTY (order applied as stock-in!)'
         ELSE 'time-correlated only' END AS verdict
FROM activity_log a
JOIN products p       ON p.id = a.subject_id
JOIN order_details od ON od.product_id = a.subject_id
JOIN orders o         ON o.id = od.order_id
WHERE a.subject_type LIKE '%Product'
  AND a.event = 'updated'
  AND JSON_EXTRACT(a.properties, '$.attributes.quantity') IS NOT NULL
  AND JSON_EXTRACT(a.properties, '$.old.quantity') IS NOT NULL
  AND CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.attributes.quantity')) AS SIGNED)
    > CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.old.quantity')) AS SIGNED)
  AND (   ABS(TIMESTAMPDIFF(SECOND, o.created_at, a.created_at)) <= 10
       OR ABS(TIMESTAMPDIFF(SECOND, o.updated_at, a.created_at)) <= 10)
  AND NOT EXISTS (
        SELECT 1 FROM stock_movements sm
         WHERE sm.product_id = a.subject_id
           AND sm.movement_type IN ('restored', 'transferred_in')
           AND ABS(TIMESTAMPDIFF(SECOND, sm.created_at, a.created_at)) <= 3)
ORDER BY a.created_at, a.id;

-- ----------------------------------------------------------------------------
-- [3] PRODUCTS AT <= 0 STOCK WHEN ADDED TO AN ORDER
--     Reconstructs the stock level at order-creation time from the last
--     activity row at or before the order's created_at.
--     NULL stock_at_order_time = no activity data that far back (unknown).
-- ----------------------------------------------------------------------------
SELECT '=== [3] PRODUCTS AT ZERO (OR UNKNOWN) STOCK WHEN ADDED TO AN ORDER ===' AS section;
SELECT * FROM (
    SELECT
        od.order_id,
        o.invoice_no,
        o.order_status,
        o.stock_affected,
        o.created_at   AS order_created_at,
        od.product_id,
        p.name         AS product,
        od.quantity    AS order_line_qty,
        (SELECT CAST(JSON_UNQUOTE(JSON_EXTRACT(a2.properties, '$.attributes.quantity')) AS SIGNED)
           FROM activity_log a2
          WHERE a2.subject_type LIKE '%Product'
            AND a2.subject_id = od.product_id
            AND JSON_EXTRACT(a2.properties, '$.attributes.quantity') IS NOT NULL
            AND a2.created_at <= o.created_at
          ORDER BY a2.created_at DESC, a2.id DESC
          LIMIT 1) AS stock_at_order_time
    FROM order_details od
    JOIN orders o   ON o.id = od.order_id
    JOIN products p ON p.id = od.product_id
) t
WHERE t.stock_at_order_time <= 0 OR t.stock_at_order_time IS NULL
ORDER BY t.order_created_at;

-- ----------------------------------------------------------------------------
-- [4] TIMELINE GAPS: consecutive activity rows where old_qty of a row does not
--     equal new_qty of the previous row -> an invisible write happened between
--     them (purchase approval via raw SQL, or a direct DB edit).
-- ----------------------------------------------------------------------------
SELECT '=== [4] TIMELINE GAPS (invisible quantity writes between activity rows) ===' AS section;
SELECT * FROM (
    SELECT
        a.subject_id AS product_id,
        a.id         AS activity_id,
        a.created_at,
        LAG(CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.attributes.quantity')) AS SIGNED))
            OVER (PARTITION BY a.subject_id ORDER BY a.created_at, a.id) AS prev_new_qty,
        CAST(JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.old.quantity')) AS SIGNED) AS this_old_qty
    FROM activity_log a
    WHERE a.subject_type LIKE '%Product'
      AND JSON_EXTRACT(a.properties, '$.attributes.quantity') IS NOT NULL
) t
WHERE t.prev_new_qty IS NOT NULL
  AND t.this_old_qty IS NOT NULL
  AND t.prev_new_qty <> t.this_old_qty
ORDER BY t.product_id, t.created_at;

-- ----------------------------------------------------------------------------
-- [5] BROKEN APPROVALS: orders shown as APPROVED whose stock was never touched
--     (the silent-failure path). These are the orders whose "validation" left
--     no deduction in the logs.
-- ----------------------------------------------------------------------------
SELECT '=== [5] APPROVED ORDERS WITH NO STOCK DEDUCTION (stock_affected=0) ===' AS section;
SELECT o.id, o.invoice_no, o.created_at, o.updated_at, o.total,
       GROUP_CONCAT(CONCAT(p.name, ' x', od.quantity) SEPARATOR ' | ') AS items
FROM orders o
LEFT JOIN order_details od ON od.order_id = o.id
LEFT JOIN products p       ON p.id = od.product_id
WHERE o.order_status = 1 AND o.stock_affected = 0
GROUP BY o.id, o.invoice_no, o.created_at, o.updated_at, o.total
ORDER BY o.created_at;

-- ----------------------------------------------------------------------------
-- [6] LEGACY ENTRIES: pre-2025 "alimentation" log (ProductEntry). Rows with
--     quantity_added recorded around order timestamps = the old observer that
--     logged any non-decreasing save as a stock entry.
-- ----------------------------------------------------------------------------
SELECT '=== [6] LEGACY product_entries CORRELATED WITH ORDERS ===' AS section;
SELECT pe.id, pe.product_id, p.name AS product, pe.quantity_added, pe.created_at,
       o.id AS order_id, o.invoice_no, od.quantity AS order_line_qty
FROM product_entries pe
JOIN products p ON p.id = pe.product_id
LEFT JOIN order_details od ON od.product_id = pe.product_id
LEFT JOIN orders o ON o.id = od.order_id
      AND (   ABS(TIMESTAMPDIFF(SECOND, o.created_at, pe.created_at)) <= 10
           OR ABS(TIMESTAMPDIFF(SECOND, o.updated_at, pe.created_at)) <= 10)
ORDER BY pe.created_at;
