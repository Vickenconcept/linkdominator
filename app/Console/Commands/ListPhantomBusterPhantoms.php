<?php

namespace App\Console\Commands;

use App\Services\PhantomBusterService;
use Illuminate\Console\Command;

class ListPhantomBusterPhantoms extends Command
{
    protected $signature = 'phantombuster:list';
    protected $description = 'List all PhantomBuster phantoms in your workspace';

    public function handle()
    {
        try {
            $service = new PhantomBusterService();
            $phantoms = $service->listPhantoms();

            $this->info("Found " . count($phantoms) . " phantoms in your workspace:\n");

            foreach ($phantoms as $phantom) {
                $name = $phantom['name'] ?? 'Unknown';
                $id = $phantom['id'] ?? $phantom['phantomId'] ?? 'N/A';
                $slug = $phantom['slug'] ?? 'N/A';

                $this->line("Name: {$name}");
                $this->line("ID: {$id}");
                $this->line("Slug: {$slug}");
                $this->line("---");
            }

            // Look for post likers phantom
            $this->info("\nSearching for LinkedIn Post Likers Export phantom...");
            $likersId = $service->findPhantomByName('linkedin post likers');
            if ($likersId) {
                $this->info("✅ Found LinkedIn Post Likers Export phantom ID: {$likersId}");
                $this->info("Add this to your .env:");
                $this->line("PHANTOMBUSTER_LINKEDIN_POST_LIKERS_PHANTOM_ID={$likersId}");
            } else {
                $this->warn("❌ No LinkedIn Post Likers Export phantom found.");
            }

            // Look for post commenters phantom
            $this->info("\nSearching for LinkedIn Post Commenters Export phantom...");
            $commentersId = $service->findPhantomByName('linkedin post commenters');
            if ($commentersId) {
                $this->info("✅ Found LinkedIn Post Commenters Export phantom ID: {$commentersId}");
                $this->info("Add this to your .env:");
                $this->line("PHANTOMBUSTER_LINKEDIN_POST_COMMENTERS_PHANTOM_ID={$commentersId}");
            } else {
                $this->warn("❌ No LinkedIn Post Commenters Export phantom found.");
            }
            
            $this->info("\nNote: Company posts are fetched using RapidAPI, not PhantomBuster.");

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}

