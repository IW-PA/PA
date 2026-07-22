-- Arbitrary "Tous les N mois" recurrence for dépenses, revenus and exceptions.
-- IDEMPOTENT: safe to run on every deploy.
-- MySQL 8 has no "ADD COLUMN IF NOT EXISTS", so guard every ALTER via information_schema.
--
-- interval_months is AUTHORITATIVE when NOT NULL. When it is NULL the legacy frequency
-- ENUM still drives the schedule (ponctuel=0, mensuel=1, bimensuel=2, trimestriel=3,
-- semestriel=6, annuel=12), so rows written before this migration keep working.

-- ---------------------------------------------------------------- expenses
SET @col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'interval_months'
);
SET @ddl := IF(@col = 0,
    'ALTER TABLE expenses ADD COLUMN interval_months INT NULL DEFAULT NULL AFTER frequency',
    'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @enum := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses'
      AND COLUMN_NAME = 'frequency' AND COLUMN_TYPE LIKE '%recurrent%'
);
SET @ddl := IF(@enum = 0,
    'ALTER TABLE expenses MODIFY COLUMN frequency ENUM(''ponctuel'', ''mensuel'', ''bimensuel'', ''trimestriel'', ''semestriel'', ''annuel'', ''recurrent'') NOT NULL',
    'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

-- Backfill from the legacy ENUM. Only rows that still have no interval are touched,
-- and 'ponctuel' deliberately stays NULL.
UPDATE expenses SET interval_months = CASE frequency
        WHEN 'mensuel'     THEN 1
        WHEN 'bimensuel'   THEN 2
        WHEN 'trimestriel' THEN 3
        WHEN 'semestriel'  THEN 6
        WHEN 'annuel'      THEN 12
    END
    WHERE interval_months IS NULL
      AND frequency IN ('mensuel', 'bimensuel', 'trimestriel', 'semestriel', 'annuel');

-- ---------------------------------------------------------------- incomes
SET @col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'incomes' AND COLUMN_NAME = 'interval_months'
);
SET @ddl := IF(@col = 0,
    'ALTER TABLE incomes ADD COLUMN interval_months INT NULL DEFAULT NULL AFTER frequency',
    'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @enum := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'incomes'
      AND COLUMN_NAME = 'frequency' AND COLUMN_TYPE LIKE '%recurrent%'
);
SET @ddl := IF(@enum = 0,
    'ALTER TABLE incomes MODIFY COLUMN frequency ENUM(''ponctuel'', ''mensuel'', ''bimensuel'', ''trimestriel'', ''semestriel'', ''annuel'', ''recurrent'') NOT NULL',
    'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

UPDATE incomes SET interval_months = CASE frequency
        WHEN 'mensuel'     THEN 1
        WHEN 'bimensuel'   THEN 2
        WHEN 'trimestriel' THEN 3
        WHEN 'semestriel'  THEN 6
        WHEN 'annuel'      THEN 12
    END
    WHERE interval_months IS NULL
      AND frequency IN ('mensuel', 'bimensuel', 'trimestriel', 'semestriel', 'annuel');

-- ---------------------------------------------------------------- exceptions
SET @col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exceptions' AND COLUMN_NAME = 'interval_months'
);
SET @ddl := IF(@col = 0,
    'ALTER TABLE exceptions ADD COLUMN interval_months INT NULL DEFAULT NULL AFTER frequency',
    'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @enum := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exceptions'
      AND COLUMN_NAME = 'frequency' AND COLUMN_TYPE LIKE '%recurrent%'
);
SET @ddl := IF(@enum = 0,
    'ALTER TABLE exceptions MODIFY COLUMN frequency ENUM(''ponctuel'', ''mensuel'', ''bimensuel'', ''trimestriel'', ''semestriel'', ''annuel'', ''recurrent'') NOT NULL',
    'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

UPDATE exceptions SET interval_months = CASE frequency
        WHEN 'mensuel'     THEN 1
        WHEN 'bimensuel'   THEN 2
        WHEN 'trimestriel' THEN 3
        WHEN 'semestriel'  THEN 6
        WHEN 'annuel'      THEN 12
    END
    WHERE interval_months IS NULL
      AND frequency IN ('mensuel', 'bimensuel', 'trimestriel', 'semestriel', 'annuel');
