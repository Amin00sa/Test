# Test
use App\Models\NewModel;

// Fake Data
$fakeData = [
    [
        'externalDataBase' => [
            'entries' => [
                'dataEntries' => [
                    ['id' => 5676, 'value' => 'A1'],
                    ['id' => 5786, 'value' => 'B1'],
                ]
            ]
        ]
    ],
    [
        'externalDataBase' => [
            'entries' => [
                'dataEntries' => [
                    ['id' => 5976, 'value' => 'A2'],
                    ['id' => 6086, 'value' => 'B2'],
                ]
            ]
        ]
    ],
];

// Simulate processing your fake data
$groupedData = collect($fakeData);

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
        return $group->map(function ($dataEntry) {
            return [
                'id' => $dataEntry['id'],
                'value' => $dataEntry['value'],
            ];
        });
    })
    ->values()
    ->toArray();

// Display the transposed data
dd($transposedData);
