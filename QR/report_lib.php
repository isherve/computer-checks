<?php
/**
 * Shared report query helpers (MySQL + SQLite).
 */

if (!function_exists('app_db_is_sqlite')) {
    function app_db_is_sqlite(PDO $pdo): bool
    {
        return $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    }
}

if (!function_exists('app_build_report')) {
    /**
     * @return array{title:string,rows:array<int,array<string,mixed>>,columns:array<int,string>,overall:bool}
     */
    function app_build_report(PDO $pdo, array $get): array
    {
        $action = isset($get['action']) ? (string)$get['action'] : '';
        $period = isset($get['period']) ? (string)$get['period'] : '';
        $overall = !empty($get['overall']);
        $sqlite = app_db_is_sqlite($pdo);

        $dateExpr = $sqlite ? 'date(date)' : 'DATE(date)';
        $monthExpr = $sqlite ? "strftime('%Y-%m', date)" : "DATE_FORMAT(date, '%Y-%m')";
        $yearExpr = $sqlite ? "strftime('%Y', date)" : 'YEAR(date)';

        $query = '';
        $title = '';
        $params = [];
        $columns = [];

        if ($period === 'all' || $period === '') {
            $query = 'SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs ORDER BY date DESC LIMIT 500';
            $title = 'All Gate Logs (latest 500)';
            $overall = true;
            $columns = ['No', 'Serial Number', 'Model', 'Owner Type', 'Owner ID', 'Owner Name', 'Status', 'Check Time', 'Comment'];
        } elseif ($overall) {
            $columns = ['No', 'Serial Number', 'Model', 'Owner Type', 'Owner ID', 'Owner Name', 'Status', 'Check Time', 'Comment'];
            switch ($period) {
                case 'daily':
                    $date = (string)($get['date'] ?? '');
                    $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE {$dateExpr} = :date ORDER BY date DESC";
                    $title = 'Overall Daily Report on ' . $date;
                    $params = [':date' => $date];
                    break;
                case 'weekly':
                    $startDate = (string)($get['start_date'] ?? '');
                    $endDate = (string)($get['end_date'] ?? '');
                    $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE {$dateExpr} BETWEEN :start_date AND :end_date ORDER BY date DESC";
                    $title = 'Overall Weekly Report from ' . $startDate . ' to ' . $endDate;
                    $params = [':start_date' => $startDate, ':end_date' => $endDate];
                    break;
                case 'monthly':
                    $month = (string)($get['month'] ?? '');
                    $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE {$monthExpr} = :month ORDER BY date DESC";
                    $title = 'Overall Monthly Report for ' . $month;
                    $params = [':month' => $month];
                    break;
                case 'annual':
                    $year = (string)($get['year'] ?? '');
                    $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE {$yearExpr} = :year ORDER BY date DESC";
                    $title = 'Overall Annual Report for ' . $year;
                    $params = [':year' => $year];
                    break;
                case 'individual':
                    $date = (string)($get['date'] ?? '');
                    $sn = (string)($get['sn'] ?? '');
                    $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE {$dateExpr} = :date AND sn = :sn ORDER BY date DESC";
                    $title = 'Overall Individual Report on ' . $date . ' for SN ' . $sn;
                    $params = [':date' => $date, ':sn' => $sn];
                    break;
                default:
                    throw new InvalidArgumentException('Invalid period selected.');
            }
        } else {
            $columns = ['No', 'SN', 'Owner Type', 'Name', 'Check Time', 'Comment'];
            $label = $action === 'check-in' ? 'Checked In' : 'Checked Out';
            switch ($period) {
                case 'daily':
                    $date = (string)($get['date'] ?? '');
                    $start_hour = str_pad((string)($get['start_hour'] ?? '0'), 2, '0', STR_PAD_LEFT);
                    $start_minute = str_pad((string)($get['start_minute'] ?? '0'), 2, '0', STR_PAD_LEFT);
                    $end_hour = str_pad((string)($get['end_hour'] ?? '23'), 2, '0', STR_PAD_LEFT);
                    $end_minute = str_pad((string)($get['end_minute'] ?? '59'), 2, '0', STR_PAD_LEFT);
                    $start_datetime = $date . ' ' . $start_hour . ':' . $start_minute . ':00';
                    $end_datetime = $date . ' ' . $end_hour . ':' . $end_minute . ':59';
                    $query = 'SELECT log_id, sn, type, owname AS name, date AS check_time, comment
                              FROM logs
                              WHERE action = :action AND date BETWEEN :start_datetime AND :end_datetime
                              ORDER BY date DESC';
                    $title = "Daily Report of Computers {$label} on {$date} from {$start_hour}:{$start_minute} to {$end_hour}:{$end_minute}";
                    $params = [
                        ':action' => $action,
                        ':start_datetime' => $start_datetime,
                        ':end_datetime' => $end_datetime,
                    ];
                    break;
                case 'weekly':
                    $startDate = (string)($get['start_date'] ?? '');
                    $endDate = (string)($get['end_date'] ?? '');
                    $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND {$dateExpr} BETWEEN :start_date AND :end_date ORDER BY date DESC";
                    $title = "Weekly Report of Computers {$label} from {$startDate} to {$endDate}";
                    $params = [':action' => $action, ':start_date' => $startDate, ':end_date' => $endDate];
                    break;
                case 'monthly':
                    $month = (string)($get['month'] ?? '');
                    $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND {$monthExpr} = :month ORDER BY date DESC";
                    $title = "Monthly Report of Computers {$label} for {$month}";
                    $params = [':action' => $action, ':month' => $month];
                    break;
                case 'annual':
                    $year = (string)($get['year'] ?? '');
                    $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND {$yearExpr} = :year ORDER BY date DESC";
                    $title = "Annual Report of Computers {$label} for {$year}";
                    $params = [':action' => $action, ':year' => $year];
                    break;
                case 'individual':
                    $date = (string)($get['date'] ?? '');
                    $sn = (string)($get['sn'] ?? '');
                    $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND {$dateExpr} = :date AND sn = :sn ORDER BY date DESC";
                    $title = "Individual Report of Computers {$label} on {$date} with SN {$sn}";
                    $params = [':action' => $action, ':date' => $date, ':sn' => $sn];
                    break;
                default:
                    throw new InvalidArgumentException('Invalid period selected.');
            }
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'title' => $title,
            'rows' => $rows,
            'columns' => $columns,
            'overall' => $overall || $period === 'all' || $period === '',
        ];
    }
}

if (!function_exists('app_report_flat_rows')) {
    /** Flatten rows for CSV/table using overall vs filtered shape. */
    function app_report_flat_rows(array $report): array
    {
        $out = [];
        $no = 1;
        foreach ($report['rows'] as $row) {
            if ($report['overall']) {
                $out[] = [
                    $no++,
                    $row['sn'] ?? '',
                    $row['model'] ?? '',
                    $row['type'] ?? '',
                    $row['owno'] ?? '',
                    $row['owname'] ?? '',
                    $row['action'] ?? '',
                    $row['date'] ?? '',
                    $row['comment'] ?? '',
                ];
            } else {
                $out[] = [
                    $no++,
                    $row['sn'] ?? '',
                    $row['type'] ?? '',
                    $row['name'] ?? '',
                    $row['check_time'] ?? '',
                    $row['comment'] ?? '',
                ];
            }
        }
        return $out;
    }
}
