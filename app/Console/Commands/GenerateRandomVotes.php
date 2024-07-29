<?php

namespace App\Console\Commands;

use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRandomVotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-random-votes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar votos aleatorios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $voters = Voter::all();
        $candidates = Voter::where('is_candidate', 1)->get();

        if ($candidates->isEmpty()) {
            $this->error('No hay candidatos disponibles.');
            return;
        }

        foreach($voters as $voter) {
            $hasVoted = Vote::where('candidate_id', $voter->id)->exists();
            
            if (!$hasVoted) {
                $randomCandidate = $candidates->random();
                Vote::create([
                    'candidate_id' => $voter->id,
                    'candidate_voted_id' => $randomCandidate->id,
                    'date' => Carbon::now()->format('Y-m-d'),
                ]);
            }
        }

        $this->info('Votos generados con éxito.');
    }
}
