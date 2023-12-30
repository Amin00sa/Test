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

// Filter based on validated data
$validatedData = [
    'key' => null,
    'value' => null,
];

// Use Laravel's collection methods to transpose and filter the data
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
    ->map(function ($group) use ($validatedData) {
        return $group->filter(function ($dataEntry) use ($validatedData) {
            return (is_null($validatedData['key']) || $dataEntry['key'] === $validatedData['key'])
                && (is_null($validatedData['value']) || str_contains($dataEntry['value'], $validatedData['value']));
        });
    })
    ->values()
    ->toArray();

// Display the transposed and filtered data
dd($transposedData);
