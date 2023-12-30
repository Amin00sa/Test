# Test
use App\Models\NewModel;

// Fake Data
$fakeData = [
    [
        'externalDataBase' => [
            'entries' => [
                'name' => 'Entry1',
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
                'name' => 'Entry2',
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
        return $externalDataBase['externalDataBase']['entries']->flatMap(function ($entry) use ($externalDataBase) {
            return array_map(function ($dataEntry) use ($entry) {
                return [
                    'id' => $dataEntry['id'],
                    'value' => $dataEntry['value'],
                    'key' => $entry['name'],
                ];
            }, $entry['dataEntries']);
        });
    })
    ->groupBy(function ($item, $key) use ($fakeData) {
        return $fakeData[$key]['externalDataBase']['entries']['name'];
    })
    ->values()
    ->toArray();

// Display the transposed data
dd($transposedData);
