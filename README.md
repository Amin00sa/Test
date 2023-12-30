# Test
// Use Laravel's collection methods to transpose the data
$transposedData = $groupedData
    ->flatMap(function ($externalDataBase) {
        return $externalDataBase['externalDataBase']['entries']->flatMap(function ($entry) {
            return $entry['dataEntries'];
        });
    })
    ->groupBy(function ($item, $key) {
        return $key % count($externalDataBase['externalDataBase']['entries']);
    })
    ->map(function ($group) {
        return $group->pluck('value')->implode(' ');
    })
    ->values()
    ->toArray();
