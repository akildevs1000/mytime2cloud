<?php
namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class IncreaseBranchLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'increase:branch-limit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increase branch limit for a company';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Ask for branch limit
        $branchLimit = $this->ask('Enter the new branch limit');

        // Ask for secret (hidden)
        $secret = $this->secret('Enter the secret key');

        // Your pre-hashed secret (hash once and hardcode here)
        $storedHash = '$2y$10$KmRJ/CnHvQ03zoZSB4vEMOvvtotyFRx/NGSCpqfy8Nm4HDmO3Xy3y'; // example

        // Verify secret
        if (!Hash::check($secret, $storedHash)) {
            $this->error('Invalid secret. Operation aborted.');
            return Command::FAILURE;
        }

        // Update the company record
        Company::where("id", 1)->update(["max_branches" => $branchLimit]);

        $this->info("Branch limit updated successfully to {$branchLimit}");

        return Command::SUCCESS;
    }
}
