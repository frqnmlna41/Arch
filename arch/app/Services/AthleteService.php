<?php
namespace App\Services;

use App\Models\Athlete;
use Illuminate\Support\Facades\Storage;

class AthleteService
{
    public function store(array $data)
    {
        if (isset($data['photo'])) {
            $data['photo'] = $data['photo']->store('athletes');
        }

        return Athlete::create($data);
    }

    public function update(Athlete $athlete, array $data)
    {
        if (isset($data['photo'])) {
            if ($athlete->photo) {
                Storage::delete($athlete->photo);
            }
            $data['photo'] = $data['photo']->store('athletes');
        }

        $athlete->update($data);
        return $athlete;
    }

    public function delete(Athlete $athlete)
    {
        return $athlete->delete();
    }
}
