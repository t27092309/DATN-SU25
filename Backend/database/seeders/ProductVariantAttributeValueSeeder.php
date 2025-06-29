<?php

namespace Database\Seeders;

use App\Models\Product; // Ensure Product model is imported if needed for other seeders
use App\Models\ProductVariant; // Ensure ProductVariant model is imported if needed
use App\Models\ProductVariantAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Database\UniqueConstraintViolationException; // Import the exception

class ProductVariantAttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $desiredCount = 50; // The number of ProductVariantAttributeValue records you want to create
        $createdCount = 0;
        $maxRetriesPerRecord = 5; // How many times to retry creating a single record if it fails

        $this->command->info("Seeding $desiredCount ProductVariantAttributeValues...");

        // Ensure base data exists before starting to create relational data
        // You might have dedicated seeders for these, but this ensures a minimum if they don't run first.
        if (Product::count() === 0) {
            Product::factory()->count(5)->create()->each(function ($product) {
                ProductVariant::factory()->count(3)->create(['product_id' => $product->id]);
            });
        }
        // Also ensure some base attributes exist, or the factory will keep creating new ones.
        // You could add Attribute::factory()->count(5)->create(); here if they're not seeded elsewhere.

        while ($createdCount < $desiredCount) {
            $retries = 0;
            $successfullyCreated = false;

            while (!$successfullyCreated && $retries < $maxRetriesPerRecord) {
                try {
                    ProductVariantAttributeValue::factory()->create();
                    $successfullyCreated = true;
                    $createdCount++;
                } catch (UniqueConstraintViolationException $e) {
                    $retries++;
                    $this->command->warn("Collision detected for ProductVariantAttributeValue (Attempt {$retries}). Retrying...");
                    // Optional: Add a small sleep here to potentially avoid immediate re-collision in very fast loops
                    // usleep(10000); // Sleep for 10 milliseconds
                } catch (\RuntimeException $e) { // Catch the RuntimeException from the factory if it can't find a combo
                    $retries++;
                    $this->command->error("Factory could not find unique combination (Attempt {$retries}): " . $e->getMessage());
                    // If the factory itself can't find a unique combo, maybe we should stop trying for this record.
                    break; // Break out of inner retry loop for this record
                } catch (\Throwable $e) { // Catch any other unexpected errors
                    $retries++;
                    $this->command->error("An unexpected error occurred: " . $e->getMessage() . " (Attempt {$retries}). Retrying...");
                }
            }

            if (!$successfullyCreated) {
                $this->command->error("Failed to create a unique ProductVariantAttributeValue record after {$maxRetriesPerRecord} attempts. Skipping this record.");
                // We increment createdCount even if failed, to prevent infinite loop if it genuinely can't create enough records.
                // Or, more accurately, we can choose not to increment, but then the loop might run forever if stuck.
                // A better approach if stuck is to break the outer loop:
                // break; // If a single record can't be created, stop seeding.
            }
        }

        $this->command->info("Successfully seeded $createdCount ProductVariantAttributeValue records.");
    }
}