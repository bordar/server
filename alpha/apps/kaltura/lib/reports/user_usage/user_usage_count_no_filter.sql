SELECT COUNT(DISTINCT kuser_id) count_all
FROM borhandw.dwh_hourly_user_usage
WHERE partner_id = {PARTNER_ID}
AND date_id BETWEEN {FROM_DATE_ID} AND {TO_DATE_ID}
	