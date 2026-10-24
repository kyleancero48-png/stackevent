-- ============================================================
-- StackEvent - SQL Transactions Lab (MySQL / MariaDB)
-- ============================================================
-- Run database.sql first. Then open this file in phpMyAdmin's
-- SQL tab (or Import > transactions.sql) and execute it.
--
-- What this file demonstrates:
--   1. ROLLBACK     - undo changes made since START TRANSACTION
--   2. COMMIT       - permanently save a group of related changes
--   3. SAVEPOINT    - roll back only part of a transaction
--   4. AUTOCOMMIT=0 - keep changes pending until COMMIT
--
-- All rows created here use the "TXN LAB" marker and the
-- example.test email domain. They are removed again in section 5,
-- so real StackEvent data is not affected.
--
-- Tip: if you run one section at a time and stop halfway through
-- a transaction, run ROLLBACK; and SET autocommit = 1; before moving on.

SET NAMES utf8mb4;
SET autocommit = 1;

-- Remove leftovers from any previous lab run before starting.
DELETE FROM events
WHERE event_name = '[TXN LAB] Committed event';

DELETE FROM participants
WHERE email IN (
    'txn.lab.rollback@example.test',
    'txn.lab.committed@example.test',
    'txn.lab.autocommit@example.test'
);

DELETE FROM organizers
WHERE email IN (
    'txn.lab.kept@example.test',
    'txn.lab.savepoint.keep@example.test',
    'txn.lab.savepoint.undo@example.test'
);


-- ============================================================
-- 1. ROLLBACK
-- ============================================================
-- START TRANSACTION groups the statements that follow.
-- The INSERT is visible inside the transaction, but it is not
-- permanent until COMMIT. ROLLBACK throws the change away.

START TRANSACTION;

INSERT INTO participants (first_name, last_name, email, gender, birthdate, city)
VALUES ('TXN LAB', 'Rolled Back', 'txn.lab.rollback@example.test', 'Other', '2000-01-01', 'Cebu City');

SELECT '1a. inside transaction' AS checkpoint, COUNT(*) AS rows_visible
FROM participants
WHERE email = 'txn.lab.rollback@example.test';

ROLLBACK;

SELECT '1b. after ROLLBACK' AS checkpoint, COUNT(*) AS rows_visible
FROM participants
WHERE email = 'txn.lab.rollback@example.test';


-- ============================================================
-- 2. COMMIT
-- ============================================================
-- These four INSERTs form one unit of work: organizer, event,
-- participant, and registration. COMMIT saves all of them.
-- If any statement failed, the whole unit should be rolled back
-- instead (that is what the PHP registration flow also does).

START TRANSACTION;

INSERT INTO organizers (organizer_name, email, contact_number)
VALUES ('[TXN LAB] Kept Organizer', 'txn.lab.kept@example.test', '09170000001');
SET @txn_organizer_id = LAST_INSERT_ID();

INSERT INTO events (event_name, event_date, venue, organizer_id)
VALUES ('[TXN LAB] Committed event', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'TXN LAB Hall', @txn_organizer_id);
SET @txn_event_id = LAST_INSERT_ID();

INSERT INTO participants (first_name, last_name, email, gender, birthdate, city)
VALUES ('TXN LAB', 'Committed', 'txn.lab.committed@example.test', 'Other', '2000-01-02', 'Cebu City');
SET @txn_participant_id = LAST_INSERT_ID();

INSERT INTO registrations (event_id, participant_id)
VALUES (@txn_event_id, @txn_participant_id);

SELECT '2a. inside transaction' AS checkpoint, COUNT(*) AS rows_visible
FROM registrations
WHERE event_id = @txn_event_id AND participant_id = @txn_participant_id;

COMMIT;

SELECT '2b. after COMMIT' AS checkpoint, COUNT(*) AS rows_visible
FROM registrations
WHERE event_id = @txn_event_id AND participant_id = @txn_participant_id;


-- ============================================================
-- 3. SAVEPOINT + ROLLBACK TO SAVEPOINT
-- ============================================================
-- A savepoint is a marker inside a transaction. Here the second
-- organizer is a mistake. ROLLBACK TO SAVEPOINT removes only that
-- mistake; the first organizer can still be committed normally.

START TRANSACTION;

INSERT INTO organizers (organizer_name, email, contact_number)
VALUES ('[TXN LAB] Savepoint Keeper', 'txn.lab.savepoint.keep@example.test', '09170000002');

SAVEPOINT after_keeper;

INSERT INTO organizers (organizer_name, email, contact_number)
VALUES ('[TXN LAB] Savepoint Undone', 'txn.lab.savepoint.undo@example.test', '09170000003');

SELECT '3a. both inserts pending' AS checkpoint,
       SUM(email = 'txn.lab.savepoint.keep@example.test') AS keeper_rows,
       SUM(email = 'txn.lab.savepoint.undo@example.test') AS undone_rows
FROM organizers
WHERE email IN ('txn.lab.savepoint.keep@example.test', 'txn.lab.savepoint.undo@example.test');

ROLLBACK TO SAVEPOINT after_keeper;

SELECT '3b. after partial rollback' AS checkpoint,
       SUM(email = 'txn.lab.savepoint.keep@example.test') AS keeper_rows,
       SUM(email = 'txn.lab.savepoint.undo@example.test') AS undone_rows
FROM organizers
WHERE email IN ('txn.lab.savepoint.keep@example.test', 'txn.lab.savepoint.undo@example.test');

COMMIT;

SELECT '3c. keeper after COMMIT' AS checkpoint,
       SUM(email = 'txn.lab.savepoint.keep@example.test') AS keeper_rows,
       SUM(email = 'txn.lab.savepoint.undo@example.test') AS undone_rows
FROM organizers
WHERE email IN ('txn.lab.savepoint.keep@example.test', 'txn.lab.savepoint.undo@example.test');


-- ============================================================
-- 4. AUTOCOMMIT = 0
-- ============================================================
-- Autocommit is ON by default: every statement commits itself.
-- Turning it OFF makes the INSERT pending until COMMIT. Because
-- we ROLLBACK instead, the participant is discarded.

SELECT '4a. autocommit before' AS checkpoint, @@autocommit AS autocommit_value;

SET autocommit = 0;

SELECT '4b. autocommit turned off' AS checkpoint, @@autocommit AS autocommit_value;

INSERT INTO participants (first_name, last_name, email, gender, birthdate, city)
VALUES ('TXN LAB', 'Autocommit', 'txn.lab.autocommit@example.test', 'Other', '2000-01-03', 'Cebu City');

SELECT '4c. insert pending' AS checkpoint, COUNT(*) AS rows_visible
FROM participants
WHERE email = 'txn.lab.autocommit@example.test';

ROLLBACK;

SELECT '4d. after ROLLBACK' AS checkpoint, COUNT(*) AS rows_visible
FROM participants
WHERE email = 'txn.lab.autocommit@example.test';

SET autocommit = 1;

SELECT '4e. autocommit restored' AS checkpoint, @@autocommit AS autocommit_value;


-- ============================================================
-- 5. CLEAN UP LAB DATA
-- ============================================================
-- Real StackEvent rows are not deleted: these filters match only
-- the records inserted by this lab. Event and participant deletes
-- also cascade to the demo registration.

START TRANSACTION;

DELETE FROM events
WHERE event_name = '[TXN LAB] Committed event';

DELETE FROM participants
WHERE email IN (
    'txn.lab.rollback@example.test',
    'txn.lab.committed@example.test',
    'txn.lab.autocommit@example.test'
);

DELETE FROM organizers
WHERE email IN (
    'txn.lab.kept@example.test',
    'txn.lab.savepoint.keep@example.test',
    'txn.lab.savepoint.undo@example.test'
);

COMMIT;

SELECT '5. cleanup complete' AS checkpoint,
       (SELECT COUNT(*) FROM participants
        WHERE email LIKE 'txn.lab.%@example.test')
     + (SELECT COUNT(*) FROM organizers
        WHERE email LIKE 'txn.lab.%@example.test')
     + (SELECT COUNT(*) FROM events
        WHERE event_name LIKE '[TXN LAB]%') AS demo_rows_remaining;
