<?php 

$tables = ['groupes', 'roles','avis','cas','categoriesdossiers','choix_non_recours','comportements','motifs_greffe','nationalites','naturesdossiers','objetsdemandes','occasions','operations','pays','prisons','professions','provinces','sourcesdemandes','statut_requettes','tribunaux','typesdossiers','typesmotifsdossiers','typespjs','typesrequettes','villes'];

$numTypes = ['int', 'bigint', 'smallint', 'tinyint', 'bit', 'decimal', 'numeric', 'float', 'real', 'money'];
$sql = '';

foreach ($tables as $t) {
    $cols = collect(DB::select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION", [$t]))->pluck('DATA_TYPE', 'COLUMN_NAME');
    $idRow = DB::selectOne("SELECT name FROM sys.identity_columns WHERE object_id = OBJECT_ID(?)", ["dbo.$t"]);
    $identity = $idRow ? $idRow->name : null;
    $order = $identity ? $identity : $cols->keys()->first();
    $rows = DB::select("SELECT * FROM [dbo].[$t] ORDER BY [$order]");
    if (count($rows) == 0) continue;

    $colList = $cols->keys()->map(function ($c) { return "[$c]"; })->implode(', ');
    $sql .= "GO\n";
    if ($identity) $sql .= "SET IDENTITY_INSERT [dbo].[$t] ON \n\n";

    foreach ($rows as $r) {
        $vals = [];
        foreach ($cols as $c => $type) {
            $v = $r->$c;
            if ($v === null) { $vals[] = 'NULL'; continue; }
            if ($v instanceof \DateTimeInterface) $v = $v->format('Y-m-d H:i:s.v');

            if ($type == 'datetime' || $type == 'smalldatetime') {
                $vals[] = "CAST(N'" . str_replace(' ', 'T', substr($v, 0, 23)) . "' AS DateTime)";
            } elseif ($type == 'datetime2') {
                $vals[] = "CAST(N'" . str_replace(' ', 'T', $v) . "' AS DateTime2)";
            } elseif ($type == 'date') {
                $vals[] = "CAST(N'$v' AS Date)";
            } elseif (in_array($type, $numTypes)) {
                $vals[] = is_bool($v) ? (string) (int) $v : (string) $v;
            } else {
                $vals[] = "N'" . str_replace("'", "''", $v) . "'";
            }
        }
        $sql .= "INSERT [dbo].[$t] ($colList) VALUES (" . implode(', ', $vals) . ")\n";
    }

    if ($identity) $sql .= "SET IDENTITY_INSERT [dbo].[$t] OFF\n";
}
$sql .= "GO\n";

file_put_contents(storage_path('app/parametrage.sql'), $sql);
echo "OK : " . storage_path('app/parametrage.sql') . "\n";