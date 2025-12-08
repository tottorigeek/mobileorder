-- 各店舗に営業時間・定休日のダミーデータを追加
-- 既存の店舗データに営業時間と定休日の設定を追加します

SET NAMES utf8mb4;

-- ============================================
-- 店舗1: レストラン イタリアン
-- 毎週月曜日 + 毎月第1火曜日が定休日
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(
        'monday',
        JSON_OBJECT('type', 'monthly', 'day', 'tuesday', 'week', 1)
    ),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', true),
        'tuesday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:30', 'close', '23:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:30', 'close', '23:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop001';

-- ============================================
-- 店舗2: カフェ モカ
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(),
    'temporaryHolidays', JSON_ARRAY('2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '07:00', 'close', '21:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '08:00', 'close', '21:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '08:00', 'close', '20:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop002';

-- ============================================
-- 店舗3: 和食 さくら
-- 毎週火曜日 + 毎月最終金曜日が定休日
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(
        'tuesday',
        JSON_OBJECT('type', 'monthly', 'day', 'friday', 'week', -1)
    ),
    'temporaryHolidays', JSON_ARRAY('2024-12-30', '2024-12-31', '2025-01-01', '2025-01-02', '2025-01-03'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', true),
        'wednesday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false)
    )
)
WHERE `code` = 'shop003';

-- ============================================
-- 店舗4: バーガーショップ ビーフ
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(),
    'temporaryHolidays', JSON_ARRAY('2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '10:00', 'close', '23:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '10:00', 'close', '23:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop004';

-- ============================================
-- 店舗5: パスタハウス マンマ
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY('monday'),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', true),
        'tuesday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop005';

-- ============================================
-- 店舗6: ステーキハウス プレミアム
-- 毎週月・火曜日 + 毎月第2水曜日が定休日
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(
        'monday',
        'tuesday',
        JSON_OBJECT('type', 'monthly', 'day', 'wednesday', 'week', 2)
    ),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2024-12-31', '2025-01-01', '2025-01-02'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', true),
        'tuesday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', true),
        'wednesday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '17:00', 'close', '23:30', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '17:00', 'close', '23:30', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop006';

-- ============================================
-- 店舗7: ラーメン屋 こだわり
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY('wednesday'),
    'temporaryHolidays', JSON_ARRAY('2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', true),
        'thursday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop007';

-- ============================================
-- 店舗8: スイーツカフェ スイート
-- ============================================
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY('tuesday'),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', true),
        'wednesday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '10:00', 'close', '21:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '10:00', 'close', '21:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop008';

-- ============================================
-- 確認用: 設定された営業時間を表示
-- ============================================
-- SELECT 
--     code,
--     name,
--     JSON_EXTRACT(settings, '$.regularHolidays') as regular_holidays,
--     JSON_EXTRACT(settings, '$.temporaryHolidays') as temporary_holidays,
--     JSON_EXTRACT(settings, '$.businessHours.monday') as monday_hours
-- FROM shops
-- WHERE settings IS NOT NULL
-- ORDER BY code;

